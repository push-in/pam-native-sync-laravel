<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Domain;
final readonly class PushOutcome{public function __construct(public string $operationIdentifier,public OperationStatus $status,public ?string $message=null){}}
