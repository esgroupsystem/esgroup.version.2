<?php

declare(strict_types=1);

namespace App\Enums;

enum LeaveActionType: string
{
    case FirstNotice = 'first';
    case SecondNotice = 'second';
    case Terminate = 'terminate';
    case Cancel = 'cancel';
    case Ready = 'ready';

    public function requiresProof(): bool
    {
        return in_array($this, [self::FirstNotice, self::SecondNotice, self::Terminate], true);
    }
}
