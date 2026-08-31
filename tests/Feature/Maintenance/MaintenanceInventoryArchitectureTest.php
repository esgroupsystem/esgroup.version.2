<?php

declare(strict_types=1);

namespace Tests\Feature\Maintenance;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Services\Maintenance\PartsOutService;
use App\Services\Maintenance\ProductCatalogService;
use App\Services\Maintenance\ReceivingService;
use App\Services\Maintenance\StockTransferCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MaintenanceInventoryArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_catalog_service_creates_product_and_location_stock_rows(): void
    {
        $category = Category::query()->create(['name' => 'Electrical']);

        $product = app(ProductCatalogService::class)->create([
            'category_id' => $category->id,
            'product_name' => 'Headlight Bulb',
            'unit' => 'pcs',
        ]);

        self::assertSame('Headlight Bulb', $product->product_name);
        self::assertSame(Location::query()->count(), ProductStock::query()->where('product_id', $product->id)->count());
    }

    public function test_parts_out_service_deducts_locked_location_stock(): void
    {
        [$product, $location] = $this->makeStockedProduct(10);

        $partsOut = app(PartsOutService::class)->create([
            'location_id' => $location->id,
            'mechanic_name' => 'Test Mechanic',
            'issued_date' => '2026-08-31',
            'product_id' => [$product->id],
            'qty_used' => [3],
            'item_remarks' => [null],
        ], null);

        self::assertSame('posted', $partsOut->status);
        self::assertSame(7, ProductStock::query()->where('product_id', $product->id)->where('location_id', $location->id)->value('qty'));
        self::assertDatabaseHas('parts_out_items', ['parts_out_id' => $partsOut->id, 'product_id' => $product->id, 'qty_used' => 3]);
    }

    public function test_receiving_service_increases_stock_and_supports_partial_rollback(): void
    {
        [$product, $location] = $this->makeStockedProduct(2);

        $receiving = app(ReceivingService::class)->create([
            'location_id' => $location->id,
            'delivered_by' => 'Supplier',
            'delivery_date' => '2026-08-31',
            'product_id' => [$product->id],
            'qty_delivered' => [5],
        ], null, null);

        self::assertSame(7, ProductStock::query()->where('product_id', $product->id)->where('location_id', $location->id)->value('qty'));

        $item = $receiving->items()->firstOrFail();
        app(ReceivingService::class)->rollbackItem($receiving->id, $item->id, 2, null);

        self::assertSame(5, ProductStock::query()->where('product_id', $product->id)->where('location_id', $location->id)->value('qty'));
        self::assertSame(2, $item->refresh()->qty_rolled_back);
    }

    public function test_stock_transfer_service_moves_stock_atomically_between_locations(): void
    {
        [$product, $from] = $this->makeStockedProduct(8);
        $to = Location::query()->whereKeyNot($from->id)->firstOrFail();

        $transfer = app(StockTransferCreationService::class)->create([
            'from_location_id' => $from->id,
            'to_location_id' => $to->id,
            'transfer_date' => '2026-08-31',
            'product_id' => [$product->id],
            'qty' => [3],
        ], null);

        self::assertSame('completed', $transfer->status);
        self::assertSame(5, ProductStock::query()->where('product_id', $product->id)->where('location_id', $from->id)->value('qty'));
        self::assertSame(3, ProductStock::query()->where('product_id', $product->id)->where('location_id', $to->id)->value('qty'));
    }

    /** @return array{Product, Location} */
    private function makeStockedProduct(int $quantity): array
    {
        $category = Category::query()->create(['name' => 'Category '.uniqid()]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'product_name' => 'Product '.uniqid(),
        ]);
        $location = Location::query()->firstOrFail();
        ProductStock::query()->where('product_id', $product->id)->where('location_id', $location->id)->update(['qty' => $quantity]);
        $product->update(['stock_qty' => $quantity]);

        return [$product, $location];
    }
}
