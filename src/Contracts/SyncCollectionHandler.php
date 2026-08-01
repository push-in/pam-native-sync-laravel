<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Contracts;use Pam\Native\LaravelSync\Domain\AppliedChange;use Pam\Native\LaravelSync\Domain\IncomingOperation;
interface SyncCollectionHandler{public function collection():string;public function apply(string $subjectIdentifier,IncomingOperation $operation):AppliedChange;}
