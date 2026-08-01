<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Tests;
use Orchestra\Testbench\TestCase as BaseTestCase;use Pam\Native\LaravelSync\LaravelSyncServiceProvider;
abstract class TestCase extends BaseTestCase
{
 protected function getPackageProviders($app):array{return[LaravelSyncServiceProvider::class];}
 protected function defineEnvironment($app):void{$app['config']->set('database.default','testing');$app['config']->set('database.connections.testing',['driver'=>'sqlite','database'=>':memory:','prefix'=>'']);$app['config']->set('pam-native-sync.middleware',['api']);$app['config']->set('pam-native-sync.cursor_key','test-secret-key');}
 protected function setUp():void{parent::setUp();$this->artisan('migrate',['--database'=>'testing'])->run();$this->actingAs(new TestUser('user-1'));}
}
