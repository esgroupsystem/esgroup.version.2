<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\StockTransferService;
use Tests\TestCase;

final class StockTransferServiceTest extends TestCase
{
    public function test_stock_transfer_rejects_same_source_and_destination_location(): void
    {
        $service = app(StockTransferService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Source and destination locations must be different.'
        );

        $service->transfer([
            'from_location_id' => 1,
            'to_location_id' => 1,
            'transfer_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 1,
                ],
            ],
        ]);
    }

    public function test_stock_transfer_rejects_non_positive_item_quantities(): void
    {
        $service = app(StockTransferService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Stock-transfer item quantities must be greater than zero.'
        );

        $service->transfer([
            'from_location_id' => 1,
            'to_location_id' => 2,
            'transfer_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 0,
                ],
            ],
        ]);
    }
}
