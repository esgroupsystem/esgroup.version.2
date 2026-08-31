<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\InventoryTransactionStatus;
use App\Enums\StockLevelStatus;
use PHPUnit\Framework\TestCase;

final class InventoryModuleEnumTest extends TestCase
{
    public function test_inventory_transaction_status_values_preserve_existing_contract(): void
    {
        self::assertSame('posted', InventoryTransactionStatus::Posted->value);
        self::assertSame('completed', InventoryTransactionStatus::Completed->value);
        self::assertSame('rolled_back', InventoryTransactionStatus::RolledBack->value);
        self::assertTrue(InventoryTransactionStatus::RolledBack->isRolledBack());
    }

    public function test_stock_level_status_is_derived_without_magic_strings(): void
    {
        self::assertSame(StockLevelStatus::Out, StockLevelStatus::fromQuantity(0));
        self::assertSame(StockLevelStatus::Low, StockLevelStatus::fromQuantity(5));
        self::assertSame(StockLevelStatus::Available, StockLevelStatus::fromQuantity(6));
    }
}
