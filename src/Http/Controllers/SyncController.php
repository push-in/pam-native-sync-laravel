<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Http\Controllers;
use Illuminate\Routing\Controller;use Pam\Native\LaravelSync\Http\Requests\SyncRequest;use Pam\Native\LaravelSync\Http\Resources\SyncEnvelopeResource;use Pam\Native\LaravelSync\Services\SyncService;
final class SyncController extends Controller{public function __invoke(SyncRequest$request,SyncService$service):SyncEnvelopeResource{$user=$request->user();return new SyncEnvelopeResource($service->synchronize((string)$user->getAuthIdentifier(),(string)$request->validated('clientId'),(array)$request->validated('operations'),$request->validated('cursor'),(int)($request->validated('limit')??100)));}}
