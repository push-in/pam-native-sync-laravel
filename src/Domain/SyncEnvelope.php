<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Domain;
final readonly class SyncEnvelope{/** @param list<PushOutcome> $outcomes @param list<AppliedChange> $changes */public function __construct(public array $outcomes,public array $changes,public string $cursor,public bool $hasMore){}}
