<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryTransactionStatus: string
{
    case Posted = 'posted';
    case Completed = 'completed';
    case RolledBack = 'rolled_back';

    public function isRolledBack(): bool
    {
        return $this === self::RolledBack;
    }
}
