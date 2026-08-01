<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Transport;

use Closure;
use JsonException;
use Pam\Native\Http\Http;
use Pam\Native\Http\HttpResponse;
use Pam\Native\LaravelSync\Domain\OperationStatus;
use Pam\Native\Sync\Contracts\SyncTransport;
use Pam\Native\Sync\RemoteChange;
use Pam\Native\Sync\SyncOperation;
use Pam\Native\Sync\SyncOperationKind;
use Pam\Native\Sync\SyncPullResult;
use Pam\Native\Sync\SyncPushResult;

final readonly class LaravelSyncTransport implements SyncTransport
{
    /** @param Closure():?string $tokenProvider */
    public function __construct(private string $endpoint,private string $clientIdentifier,private Closure $tokenProvider,private int $timeoutMs=30_000) {}

    public function push(array $operations,Closure $complete):void
    {
        $this->request(array_map(static fn(SyncOperation$operation)=>$operation->jsonSerialize(),$operations),'',1,function(?array$data,?string$error)use($complete):void{
            if($error!==null){$complete(new SyncPushResult([],[],0,$error));return;}
            $ack=[];$rejected=[];$conflicts=0;
            foreach((array)($data['outcomes']??[])as$outcome){$id=(string)($outcome['id']??'');$status=(int)($outcome['status']??0);if($id==='')continue;if($status===OperationStatus::Applied->value)$ack[]=$id;elseif($status===OperationStatus::Conflict->value){$ack[]=$id;$conflicts++;}else $rejected[$id]=(string)($outcome['message']??'Rejected by sync server.');}
            $complete(new SyncPushResult($ack,$rejected,$conflicts));
        });
    }

    public function pull(string$cursor,int$limit,Closure$complete):void
    {
        $this->request([],$cursor,$limit,function(?array$data,?string$error)use($complete):void{
            if($error!==null){$complete(new SyncPullResult($cursor,[],false,$error));return;}
            try{$changes=array_map(static fn(array$c)=>new RemoteChange((string)$c['collection'],(string)$c['recordId'],SyncOperationKind::from((int)$c['kind']),(array)$c['payload'],(int)$c['version'],(int)$c['serverTimestampMillis']),(array)($data['changes']??[]));$complete(new SyncPullResult((string)($data['cursor']??''),$changes,(bool)($data['hasMore']??false)));}catch(\Throwable$error){$complete(new SyncPullResult($cursor,[],false,'Invalid sync response: '.$error->getMessage()));}
        });
    }

    /** @param list<array<string,mixed>>$operations @param Closure(?array,?string):void$complete */
    private function request(array$operations,string$cursor,int$limit,Closure$complete):void
    {
        $token=($this->tokenProvider)();$headers=[];if(is_string($token)&&$token!=='')$headers['Authorization']='Bearer '.$token;
        Http::json('POST',$this->endpoint,['clientId'=>$this->clientIdentifier,'cursor'=>$cursor===''?null:$cursor,'limit'=>$limit,'operations'=>$operations],function(HttpResponse$response)use($complete):void{
            if(!$response->successful()){$message=$response->transportFailed()?$response->error:"Sync server returned HTTP {$response->statusCode}.";$complete(null,$message);return;}
            try{$data=json_decode($response->body,true,512,JSON_THROW_ON_ERROR);if(!is_array($data))throw new JsonException('Expected a JSON object.');$complete($data,null);}catch(JsonException$error){$complete(null,'Invalid sync JSON: '.$error->getMessage());}
        },$headers,$this->timeoutMs);
    }
}
