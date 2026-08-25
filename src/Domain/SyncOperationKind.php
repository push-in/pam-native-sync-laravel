<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Domain;

/**
 * Stable integer wire codes. This enum intentionally lives in the server
 * package so a Laravel application never needs the PAM Native client SDK.
 */
enum SyncOperationKind: int
{
    case Upsert = 1;
    case Delete = 2;
}
