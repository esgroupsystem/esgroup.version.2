<?php

declare(strict_types=1);

namespace App\Enums;

enum StockLevelStatus: string
{
    case Out = 'out';
    case Low = 'low';
    case Available = 'available';

    public static function fromQuantity(int $quantity, int $lowStockThreshold = 5): self
    {
        return match (true) {
            $quantity <= 0 => self::Out,
            $quantity <= $lowStockThreshold => self::Low,
            default => self::Available,
        };
    }
}
