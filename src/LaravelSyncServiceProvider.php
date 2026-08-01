<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync;
use Illuminate\Contracts\Foundation\Application;use Illuminate\Database\ConnectionInterface;use Illuminate\Routing\Router;use Illuminate\Support\ServiceProvider;use Pam\Native\LaravelSync\Repositories\SyncRepository;
final class LaravelSyncServiceProvider extends ServiceProvider
{
 public function register():void{$this->mergeConfigFrom(__DIR__.'/../config/pam-native-sync.php','pam-native-sync');$this->app->singleton(SyncCollectionRegistry::class);$this->app->singleton(CursorCodec::class,static fn(Application$app)=>new CursorCodec((string)$app['config']->get('pam-native-sync.cursor_key')));$this->app->singleton(SyncRepository::class,static fn(Application$app)=>new SyncRepository($app->make(ConnectionInterface::class)));}
 public function boot(Router$router):void{$this->loadMigrationsFrom(__DIR__.'/../database/migrations');$router->prefix((string)config('pam-native-sync.path'))->middleware((array)config('pam-native-sync.middleware'))->group(__DIR__.'/../routes/api.php');$this->publishes([__DIR__.'/../config/pam-native-sync.php'=>config_path('pam-native-sync.php')],'pam-native-sync-config');$this->publishes([__DIR__.'/../database/migrations'=>database_path('migrations')],'pam-native-sync-migrations');}
}
