<?php
declare(strict_types=1);use Illuminate\Support\Facades\Route;use Pam\Native\LaravelSync\Http\Controllers\SyncController;Route::post('/',SyncController::class)->name('pam-native.sync');
