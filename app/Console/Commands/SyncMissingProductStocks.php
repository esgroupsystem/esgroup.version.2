<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Console\Command;

class SyncMissingProductStocks extends Command
{
    protected $signature = 'products:sync-stocks';

    protected $description = 'Create missing product stock rows for all existing products and locations';

    public function handle()
    {
        $products = Product::all();
        $locations = Location::all();

        if ($products->isEmpty()) {
            $this->warn('No products found.');

            return Command::SUCCESS;
        }

        if ($locations->isEmpty()) {
            $this->warn('No locations found.');

            return Command::SUCCESS;
        }

        $createdCount = 0;

        foreach ($products as $product) {
            foreach ($locations as $location) {
                $stock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $location->id,
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                if ($stock->wasRecentlyCreated) {
                    $createdCount++;
                    $this->line("Created stock row: Product #{$product->id} - {$product->product_name} | Location #{$location->id} - {$location->name}");
                }
            }
        }

        $this->info("Done. {$createdCount} missing product stock row(s) created.");

        return Command::SUCCESS;
    }
}
