<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Repositories;

use Illuminate\Database\ConnectionInterface;
use Pam\Native\LaravelSync\Domain\AppliedChange;
use Pam\Native\LaravelSync\Domain\IncomingOperation;
use Pam\Native\LaravelSync\Domain\OperationStatus;
use Pam\Native\LaravelSync\Domain\PushOutcome;
use Pam\Native\Sync\SyncOperationKind;

final readonly class SyncRepository
{
    public function __construct(private ConnectionInterface $database) {}

    public function transaction(callable $callback): mixed { return $this->database->transaction($callback); }

    public function outcome(string $subject, string $client, string $operation): ?PushOutcome
    {
        $row = $this->database->table('pam_sync_operations')->where(['subject_id'=>$subject,'client_id'=>$client,'operation_id'=>$operation])->first();
        return $row ? new PushOutcome($operation, OperationStatus::from((int)$row->operation_status), $row->message === null ? null : (string)$row->message) : null;
    }

    public function recordOutcome(string $subject, IncomingOperation $operation, PushOutcome $outcome): void
    {
        $this->database->table('pam_sync_operations')->insertOrIgnore([
            'subject_id'=>$subject,'client_id'=>$operation->clientIdentifier,'operation_id'=>$operation->identifier,
            'collection_name'=>$operation->collection,'record_id'=>$operation->recordIdentifier,
            'operation_kind'=>$operation->kind->value,'operation_status'=>$outcome->status->value,
            'message'=>$outcome->message,'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function append(string $subject, AppliedChange $change): void
    {
        $this->database->table('pam_sync_changes')->insert([
            'subject_id'=>$subject,'collection_name'=>$change->collection,'record_id'=>$change->recordIdentifier,
            'operation_kind'=>$change->kind->value,'payload'=>json_encode($change->payload, JSON_THROW_ON_ERROR),
            'record_version'=>$change->version,'server_timestamp_ms'=>$change->serverTimestampMillis,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    /** @return list<AppliedChange> */
    public function changes(string $subject, int $after, int $limit): array
    {
        return $this->database->table('pam_sync_changes')->where('subject_id',$subject)->where('sequence','>',$after)->orderBy('sequence')->limit($limit)->get()->map(static fn(object $row)=>new AppliedChange(
            (string)$row->collection_name,(string)$row->record_id,SyncOperationKind::from((int)$row->operation_kind),
            (array)json_decode((string)$row->payload,true,512,JSON_THROW_ON_ERROR),(int)$row->record_version,(int)$row->server_timestamp_ms
        ))->all();
    }

    public function lastSequence(string $subject, int $after, int $limit): int
    {
        $value=$this->database->table('pam_sync_changes')->where('subject_id',$subject)->where('sequence','>',$after)->orderBy('sequence')->limit($limit)->pluck('sequence')->last();
        return $value === null ? $after : (int)$value;
    }
}
