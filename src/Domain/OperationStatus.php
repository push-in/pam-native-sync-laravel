<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync\Domain;

enum OperationStatus: int
{
    case Applied = 1;
    case Rejected = 2;
    case Conflict = 3;
    case Processing = 4;
}
