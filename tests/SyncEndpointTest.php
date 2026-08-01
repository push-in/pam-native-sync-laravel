<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Tests;use Pam\Native\LaravelSync\SyncCollectionRegistry;
final class SyncEndpointTest extends TestCase
{
 private NotesHandler$handler;
 protected function setUp():void{parent::setUp();$this->handler=new NotesHandler();$this->app->make(SyncCollectionRegistry::class)->register($this->handler);}
 private function payload(string$id='op-1',int$base=0):array{return['clientId'=>'device-1','operations'=>[['id'=>$id,'collection'=>'notes','recordId'=>'note-1','kind'=>1,'payload'=>['title'=>'Offline'],'baseVersion'=>$base,'clientTimestampMillis'=>1000]],'limit'=>100];}
 public function test_push_pull_and_idempotency():void{$first=$this->postJson('/pam-native/sync',$this->payload())->assertOk()->assertJsonPath('outcomes.0.status',1)->assertJsonPath('changes.0.version',1);$cursor=$first->json('cursor');$this->postJson('/pam-native/sync',$this->payload())->assertOk()->assertJsonCount(1,'outcomes');$this->assertSame(1,$this->handler->calls);$this->postJson('/pam-native/sync',['clientId'=>'device-1','cursor'=>$cursor,'operations'=>[]])->assertOk()->assertJsonCount(0,'changes');}
 public function test_conflict_is_typed_and_not_logged_as_change():void{$this->postJson('/pam-native/sync',$this->payload('op-conflict',99))->assertOk()->assertJsonPath('outcomes.0.status',3)->assertJsonCount(0,'changes');}
 public function test_delete_payload_and_tampered_cursor_are_rejected():void{$delete=$this->payload('op-delete');$delete['operations'][0]['kind']=2;$this->postJson('/pam-native/sync',$delete)->assertUnprocessable()->assertJsonValidationErrors('operations.0.payload');$this->postJson('/pam-native/sync',['clientId'=>'device-1','cursor'=>'tampered.cursor','operations'=>[]])->assertUnprocessable()->assertJsonValidationErrors('cursor');}
 public function test_subjects_are_isolated():void{$response=$this->postJson('/pam-native/sync',$this->payload())->assertOk();$this->actingAs(new TestUser('user-2'));$this->postJson('/pam-native/sync',['clientId'=>'device-2','cursor'=>null,'operations'=>[]])->assertOk()->assertJsonCount(0,'changes')->assertJsonMissing(['title'=>'Offline']);$this->assertNotSame($response->json('cursor'),'');}
}
