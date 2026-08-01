<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Http\Resources;use Illuminate\Http\Request;use Illuminate\Http\Resources\Json\JsonResource;
final class SyncEnvelopeResource extends JsonResource{public static $wrap=null;public function toArray(Request $request):array{return['outcomes'=>PushOutcomeResource::collection($this->resource->outcomes),'changes'=>AppliedChangeResource::collection($this->resource->changes),'cursor'=>$this->resource->cursor,'hasMore'=>$this->resource->hasMore];}}
