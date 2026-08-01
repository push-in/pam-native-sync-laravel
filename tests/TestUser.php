<?php
declare(strict_types=1);namespace Pam\Native\LaravelSync\Tests;use Illuminate\Auth\Authenticatable;use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
final class TestUser implements AuthenticatableContract{use Authenticatable;public function __construct(private readonly string$id){}public function getAuthIdentifierName():string{return'id';}public function getAuthIdentifier():mixed{return$this->id;}}
