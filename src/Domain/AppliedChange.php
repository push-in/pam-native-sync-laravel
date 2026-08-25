<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Domain;

final readonly class AppliedChange
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $collection,
        public string $recordIdentifier,
        public SyncOperationKind $kind,
        public array $payload,
        public int $version,
        public int $serverTimestampMillis,
    ) {}
}
