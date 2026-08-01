<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('pam_sync_operations',function(Blueprint$t):void{$t->id();$t->string('subject_id',128);$t->string('client_id',128);$t->string('operation_id',128);$t->string('collection_name',128);$t->string('record_id',128);$t->unsignedTinyInteger('operation_kind');$t->unsignedTinyInteger('operation_status');$t->string('message',1024)->nullable();$t->timestamps();$t->unique(['subject_id','client_id','operation_id'],'pam_sync_operation_unique');});
  Schema::create('pam_sync_changes',function(Blueprint$t):void{$t->bigIncrements('sequence');$t->string('subject_id',128)->index();$t->string('collection_name',128);$t->string('record_id',128);$t->unsignedTinyInteger('operation_kind');$t->json('payload');$t->unsignedBigInteger('record_version');$t->unsignedBigInteger('server_timestamp_ms');$t->timestamps();$t->index(['subject_id','sequence'],'pam_sync_subject_sequence');});
 }
 public function down():void{Schema::dropIfExists('pam_sync_changes');Schema::dropIfExists('pam_sync_operations');}
};
