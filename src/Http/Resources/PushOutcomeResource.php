<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Http\Resources;use Illuminate\Http\Request;use Illuminate\Http\Resources\Json\JsonResource;
final class PushOutcomeResource extends JsonResource{public static $wrap=null;public function toArray(Request $request):array{return['id'=>$this->resource->operationIdentifier,'status'=>$this->resource->status->value,'message'=>$this->resource->message];}}
