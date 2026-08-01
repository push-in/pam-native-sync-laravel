<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Domain;use Pam\Native\Sync\SyncOperationKind;
final readonly class IncomingOperation{/** @param array<string,mixed> $payload */public function __construct(public string $identifier,public string $clientIdentifier,public string $collection,public string $recordIdentifier,public SyncOperationKind $kind,public array $payload,public int $baseVersion,public int $clientTimestampMillis){}}
