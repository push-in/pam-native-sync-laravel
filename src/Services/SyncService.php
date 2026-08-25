<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Services;

use InvalidArgumentException;
use LogicException;
use Pam\Native\LaravelSync\CursorCodec;
use Pam\Native\LaravelSync\Domain\AppliedChange;
use Pam\Native\LaravelSync\Domain\IncomingOperation;
use Pam\Native\LaravelSync\Domain\OperationStatus;
use Pam\Native\LaravelSync\Domain\PushOutcome;
use Pam\Native\LaravelSync\Domain\SyncConflictException;
use Pam\Native\LaravelSync\Domain\SyncEnvelope;
use Pam\Native\LaravelSync\Domain\SyncOperationKind;
use Pam\Native\LaravelSync\Repositories\SyncRepository;
use Pam\Native\LaravelSync\SyncCollectionRegistry;
use Throwable;

final readonly class SyncService
{
    public function __construct(
        private SyncRepository $repository,
        private SyncCollectionRegistry $registry,
        private CursorCodec $cursor,
    ) {}

    /** @param list<array<string, mixed>> $rawOperations */
    public function synchronize(
        string $subject,
        string $client,
        array $rawOperations,
        ?string $cursor,
        int $limit,
    ): SyncEnvelope {
        if ($subject === '') {
            throw new InvalidArgumentException('An authenticated sync subject is required.');
        }

        $outcomes = [];
        foreach ($rawOperations as $raw) {
            $operation = new IncomingOperation(
                (string) $raw['id'],
                $client,
                (string) $raw['collection'],
                (string) $raw['recordId'],
                SyncOperationKind::from((int) $raw['kind']),
                (array) ($raw['payload'] ?? []),
                (int) $raw['baseVersion'],
                (int) $raw['clientTimestampMillis'],
            );
            $existing = $this->repository->outcome(
                $subject,
                $client,
                $operation->identifier,
            );
            if ($existing !== null && $existing->status !== OperationStatus::Processing) {
                $outcomes[] = $existing;

                continue;
            }

            $outcomes[] = $this->applyOperation($subject, $operation);
        }

        $after = $this->cursor->decode($cursor);
        $rows = $this->repository->changes($subject, $after, $limit + 1);
        $hasMore = count($rows) > $limit;
        $changes = array_slice($rows, 0, $limit);
        $sequence = $this->repository->lastSequence($subject, $after, count($changes));

        return new SyncEnvelope(
            $outcomes,
            $changes,
            $this->cursor->encode($sequence),
            $hasMore,
        );
    }

    private function applyOperation(string $subject, IncomingOperation $operation): PushOutcome
    {
        return $this->repository->transaction(function () use ($subject, $operation): PushOutcome {
            if (!$this->repository->claim($subject, $operation)) {
                $existing = $this->repository->lockedOutcome(
                    $subject,
                    $operation->clientIdentifier,
                    $operation->identifier,
                );
                if ($existing === null || $existing->status === OperationStatus::Processing) {
                    throw new LogicException('A claimed sync operation has no completed outcome.');
                }

                return $existing;
            }

            try {
                $change = $this->repository->transaction(
                    fn (): AppliedChange => $this->applyHandler($subject, $operation),
                );
                $outcome = new PushOutcome(
                    $operation->identifier,
                    OperationStatus::Applied,
                );
            } catch (SyncConflictException $error) {
                $outcome = new PushOutcome(
                    $operation->identifier,
                    OperationStatus::Conflict,
                    substr($error->getMessage(), 0, 1_024),
                );
            } catch (Throwable $error) {
                $outcome = new PushOutcome(
                    $operation->identifier,
                    OperationStatus::Rejected,
                    substr($error->getMessage(), 0, 1_024),
                );
            }

            $this->repository->completeOutcome($subject, $operation, $outcome);

            return $outcome;
        });
    }

    private function applyHandler(string $subject, IncomingOperation $operation): AppliedChange
    {
        $change = $this->registry->get($operation->collection)->apply($subject, $operation);
        if ($change->collection !== $operation->collection
            || $change->recordIdentifier !== $operation->recordIdentifier) {
            throw new InvalidArgumentException('Handler returned a change for a different record.');
        }
        $this->repository->append($subject, $change);

        return $change;
    }
}
