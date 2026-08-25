<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Tests;

use Pam\Native\LaravelSync\Domain\OperationStatus;
use Pam\Native\LaravelSync\Domain\SyncOperationKind;
use Pam\Native\LaravelSync\SyncCollectionRegistry;

final class SyncEndpointTest extends TestCase
{
    private NotesHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new NotesHandler();
        $this->app->make(SyncCollectionRegistry::class)->register($this->handler);
    }

    public function testPushPullAndIdempotency(): void
    {
        $first = $this->postJson('/pam-native/sync', $this->payload())
            ->assertOk()
            ->assertJsonPath('outcomes.0.status', OperationStatus::Applied->value)
            ->assertJsonPath('changes.0.version', 1);
        $cursor = $first->json('cursor');

        $this->postJson('/pam-native/sync', $this->payload())
            ->assertOk()
            ->assertJsonCount(1, 'outcomes');
        $this->assertSame(1, $this->handler->calls);

        $this->postJson('/pam-native/sync', [
            'clientId' => 'device-1',
            'cursor' => $cursor,
            'operations' => [],
        ])->assertOk()->assertJsonCount(0, 'changes');
    }

    public function testConflictIsTypedIdempotentAndNotLoggedAsChange(): void
    {
        $payload = $this->payload('op-conflict', 99);
        $this->postJson('/pam-native/sync', $payload)
            ->assertOk()
            ->assertJsonPath('outcomes.0.status', OperationStatus::Conflict->value)
            ->assertJsonCount(0, 'changes');
        $this->postJson('/pam-native/sync', $payload)
            ->assertOk()
            ->assertJsonPath('outcomes.0.status', OperationStatus::Conflict->value)
            ->assertJsonCount(0, 'changes');

        $this->assertSame(1, $this->handler->calls);
        $this->assertDatabaseHas('pam_sync_operations', [
            'operation_id' => 'op-conflict',
            'operation_status' => OperationStatus::Conflict->value,
        ]);
        $this->assertDatabaseMissing('pam_sync_operations', [
            'operation_status' => OperationStatus::Processing->value,
        ]);
    }

    public function testDeletePayloadAndTamperedCursorAreRejected(): void
    {
        $delete = $this->payload('op-delete');
        $delete['operations'][0]['kind'] = SyncOperationKind::Delete->value;
        $this->postJson('/pam-native/sync', $delete)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('operations.0.payload');
        $this->postJson('/pam-native/sync', [
            'clientId' => 'device-1',
            'cursor' => 'tampered.cursor',
            'operations' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors('cursor');
    }

    public function testSubjectsAreIsolated(): void
    {
        $response = $this->postJson('/pam-native/sync', $this->payload())->assertOk();
        $this->actingAs(new TestUser('user-2'));
        $this->postJson('/pam-native/sync', [
            'clientId' => 'device-2',
            'cursor' => null,
            'operations' => [],
        ])->assertOk()->assertJsonCount(0, 'changes')->assertJsonMissing([
            'title' => 'Offline',
        ]);
        $this->assertNotSame($response->json('cursor'), '');
    }

    public function testOperationStatusProtocolIsSequential(): void
    {
        $this->assertSame([1, 2, 3, 4], array_column(OperationStatus::cases(), 'value'));
        $this->assertSame([1, 2], array_column(SyncOperationKind::cases(), 'value'));
    }

    /** @return array<string, mixed> */
    private function payload(string $identifier = 'op-1', int $baseVersion = 0): array
    {
        return [
            'clientId' => 'device-1',
            'operations' => [[
                'id' => $identifier,
                'collection' => 'notes',
                'recordId' => 'note-1',
                'kind' => 1,
                'payload' => ['title' => 'Offline'],
                'baseVersion' => $baseVersion,
                'clientTimestampMillis' => 1_000,
            ]],
            'limit' => 100,
        ];
    }
}
