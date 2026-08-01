<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Services;

use InvalidArgumentException;
use Pam\Native\LaravelSync\CursorCodec;
use Pam\Native\LaravelSync\Domain\IncomingOperation;
use Pam\Native\LaravelSync\Domain\OperationStatus;
use Pam\Native\LaravelSync\Domain\PushOutcome;
use Pam\Native\LaravelSync\Domain\SyncConflictException;
use Pam\Native\LaravelSync\Domain\SyncEnvelope;
use Pam\Native\LaravelSync\Repositories\SyncRepository;
use Pam\Native\LaravelSync\SyncCollectionRegistry;
use Pam\Native\Sync\SyncOperationKind;
use Throwable;

final readonly class SyncService
{
    public function __construct(private SyncRepository $repository,private SyncCollectionRegistry $registry,private CursorCodec $cursor) {}

    /** @param list<array<string,mixed>> $rawOperations */
    public function synchronize(string $subject,string $client,array $rawOperations,?string $cursor,int $limit): SyncEnvelope
    {
        if ($subject === '') throw new InvalidArgumentException('An authenticated sync subject is required.');
        $outcomes=[];
        foreach($rawOperations as $raw){
            $operation=new IncomingOperation((string)$raw['id'],$client,(string)$raw['collection'],(string)$raw['recordId'],SyncOperationKind::from((int)$raw['kind'],),(array)($raw['payload']??[]),(int)$raw['baseVersion'],(int)$raw['clientTimestampMillis']);
            $existing=$this->repository->outcome($subject,$client,$operation->identifier);
            if($existing){$outcomes[]=$existing;continue;}
            try{
                $outcomes[]=$this->repository->transaction(function()use($subject,$operation):PushOutcome{
                    $change=$this->registry->get($operation->collection)->apply($subject,$operation);
                    if($change->collection!==$operation->collection||$change->recordIdentifier!==$operation->recordIdentifier)throw new InvalidArgumentException('Handler returned a change for a different record.');
                    $outcome=new PushOutcome($operation->identifier,OperationStatus::Applied);
                    $this->repository->append($subject,$change);$this->repository->recordOutcome($subject,$operation,$outcome);return$outcome;
                });
            }catch(SyncConflictException $error){$outcome=new PushOutcome($operation->identifier,OperationStatus::Conflict,substr($error->getMessage(),0,1024));$this->repository->recordOutcome($subject,$operation,$outcome);$outcomes[]=$outcome;
            }catch(Throwable $error){$outcome=new PushOutcome($operation->identifier,OperationStatus::Rejected,substr($error->getMessage(),0,1024));$this->repository->recordOutcome($subject,$operation,$outcome);$outcomes[]=$outcome;}
        }
        $after=$this->cursor->decode($cursor);$rows=$this->repository->changes($subject,$after,$limit+1);$hasMore=count($rows)>$limit;$changes=array_slice($rows,0,$limit);
        $sequence=$this->repository->lastSequence($subject,$after,count($changes));
        return new SyncEnvelope($outcomes,$changes,$this->cursor->encode($sequence),$hasMore);
    }
}
