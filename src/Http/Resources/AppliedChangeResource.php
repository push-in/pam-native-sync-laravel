<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Http\Resources;use Illuminate\Http\Request;use Illuminate\Http\Resources\Json\JsonResource;
final class AppliedChangeResource extends JsonResource{public static $wrap=null;public function toArray(Request $request):array{return['collection'=>$this->resource->collection,'recordId'=>$this->resource->recordIdentifier,'kind'=>$this->resource->kind->value,'payload'=>$this->resource->payload,'version'=>$this->resource->version,'serverTimestampMillis'=>$this->resource->serverTimestampMillis];}}
