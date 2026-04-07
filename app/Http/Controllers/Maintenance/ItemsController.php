<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

class ItemsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories = Category::orderBy('name')->get();

            $search = trim((string) $request->get('search', ''));
            $target = trim((string) $request->get('target', ''));

            $itemsQuery = Product::with('category')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('product_name', 'like', "%{$search}%")
                            ->orWhere('supplier_name', 'like', "%{$search}%")
                            ->orWhere('unit', 'like', "%{$search}%")
                            ->orWhere('part_number', 'like', "%{$search}%")
                            ->orWhere('details', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($categoryQuery) use ($search) {
                                $categoryQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderBy('product_name');

            $stockQuery = Product::with('category')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('product_name', 'like', "%{$search}%")
                            ->orWhere('supplier_name', 'like', "%{$search}%")
                            ->orWhere('unit', 'like', "%{$search}%")
                            ->orWhere('part_number', 'like', "%{$search}%")
                            ->orWhere('details', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($categoryQuery) use ($search) {
                                $categoryQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderBy('product_name');

            $items = $itemsQuery
                ->paginate(10, ['*'], 'items_page')
                ->appends([
                    'search' => $search,
                ]);

            $stock = $stockQuery
                ->paginate(10, ['*'], 'stock_page')
                ->appends([
                    'search' => $search,
                ]);

            if ($request->ajax()) {
                if ($target === 'items') {
                    return view('maintenance.items.items_table', compact('items'))->render();
                }

                if ($target === 'stock') {
                    return view('maintenance.items.stock_table', ['products' => $stock])->render();
                }

                return response(
                    '<div class="alert alert-danger m-3">Invalid AJAX target.</div>',
                    400
                );
            }

            return view('maintenance.items.index', compact('categories', 'items', 'stock', 'search'));
        } catch (Throwable $e) {
            Log::error('ItemsController@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            if ($request->ajax()) {
                return response(
                    '<div class="alert alert-danger m-3">Failed to load items list.</div>',
                    500
                );
            }

            flash('Failed to load items. Please try again or check the system logs.')->error();

            return back();
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $search = trim((string) $request->get('search', ''));
            $locationFilter = $request->get('location');

            $allowedFilters = ['main', 'balintawak', 'needs_transfer', null, ''];
            if (! in_array($locationFilter, $allowedFilters, true)) {
                flash('Invalid location filter selected.')->error();

                return back();
            }

            $locations = Location::orderBy('name')->get();

            $mainLocation = $locations->first(function ($loc) {
                return stripos($loc->name, 'main') !== false;
            });

            $balintawakLocation = $locations->first(function ($loc) {
                return stripos($loc->name, 'balintawak') !== false;
            });

            $productsQuery = Product::with(['category', 'stocks.location'])
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('product_name', 'like', "%{$search}%")
                            ->orWhere('part_number', 'like', "%{$search}%");
                    });
                })
                ->orderBy('product_name');

            $products = $productsQuery->get()->map(function ($product) use ($locations, $mainLocation, $balintawakLocation) {
                $locationStocks = [];

                foreach ($locations as $location) {
                    $stock = $product->stocks->firstWhere('location_id', $location->id);
                    $locationStocks[$location->id] = $stock ? (int) $stock->qty : 0;
                }

                $mainQty = $mainLocation ? ($locationStocks[$mainLocation->id] ?? 0) : 0;
                $balintawakQty = $balintawakLocation ? ($locationStocks[$balintawakLocation->id] ?? 0) : 0;
                $totalQty = collect($locationStocks)->sum();

                $product->location_stocks = $locationStocks;
                $product->main_qty = $mainQty;
                $product->balintawak_qty = $balintawakQty;
                $product->total_stock = $totalQty;

                if ($totalQty <= 0) {
                    $product->stock_status = 'out';
                } elseif ($totalQty <= 5) {
                    $product->stock_status = 'low';
                } else {
                    $product->stock_status = 'available';
                }

                $product->transfer_suggestion = null;

                if ($mainQty > 0 && $balintawakQty <= 0) {
                    $product->transfer_suggestion = 'Available in Main but zero in Balintawak';
                } elseif ($balintawakQty > 0 && $mainQty <= 0) {
                    $product->transfer_suggestion = 'Available in Balintawak but zero in Main';
                } elseif ($mainQty >= 10 && $balintawakQty <= 2) {
                    $product->transfer_suggestion = 'Needs transfer to Balintawak';
                } elseif ($balintawakQty >= 10 && $mainQty <= 2) {
                    $product->transfer_suggestion = 'Needs transfer to Main';
                }

                return $product;
            });

            if ($locationFilter === 'main') {
                $products = $products->filter(fn ($p) => $p->main_qty > 0)->values();
            } elseif ($locationFilter === 'balintawak') {
                $products = $products->filter(fn ($p) => $p->balintawak_qty > 0)->values();
            } elseif ($locationFilter === 'needs_transfer') {
                $products = $products->filter(fn ($p) => ! empty($p->transfer_suggestion))->values();
            }

            $mainStocks = $products
                ->filter(fn ($p) => $p->main_qty > 0)
                ->sortByDesc('main_qty')
                ->values();

            $balintawakStocks = $products
                ->filter(fn ($p) => $p->balintawak_qty > 0)
                ->sortByDesc('balintawak_qty')
                ->values();

            $needsTransfer = $products
                ->filter(fn ($p) => ! empty($p->transfer_suggestion))
                ->values();

            $totalItems = $products->count();
            $totalStock = $products->sum('total_stock');
            $lowStock = $products->filter(fn ($p) => $p->total_stock > 0 && $p->total_stock <= 5)->count();
            $outOfStock = $products->filter(fn ($p) => $p->total_stock <= 0)->count();

            $mainTotalStock = $products->sum('main_qty');
            $balintawakTotalStock = $products->sum('balintawak_qty');

            $mainPage = max((int) $request->get('main_page', 1), 1);
            $balintawakPage = max((int) $request->get('balintawak_page', 1), 1);
            $transferPage = max((int) $request->get('transfer_page', 1), 1);
            $perPage = 10;

            $mainStocksPaginated = new LengthAwarePaginator(
                $mainStocks->forPage($mainPage, $perPage),
                $mainStocks->count(),
                $perPage,
                $mainPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'main_page',
                    'query' => request()->query(),
                ]
            );

            $balintawakStocksPaginated = new LengthAwarePaginator(
                $balintawakStocks->forPage($balintawakPage, $perPage),
                $balintawakStocks->count(),
                $perPage,
                $balintawakPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'balintawak_page',
                    'query' => request()->query(),
                ]
            );

            $needsTransferPaginated = new LengthAwarePaginator(
                $needsTransfer->forPage($transferPage, $perPage),
                $needsTransfer->count(),
                $perPage,
                $transferPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'transfer_page',
                    'query' => request()->query(),
                ]
            );

            return view('maintenance.items.dashboard', compact(
                'search',
                'locationFilter',
                'mainLocation',
                'balintawakLocation',
                'mainStocksPaginated',
                'balintawakStocksPaginated',
                'needsTransferPaginated',
                'totalItems',
                'totalStock',
                'lowStock',
                'outOfStock',
                'mainTotalStock',
                'balintawakTotalStock'
            ));
        } catch (Throwable $e) {
            Log::error('ItemsController@dashboard failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            flash('Failed to load stock dashboard. Please check locations, stocks, or system logs.')->error();

            return back();
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ], [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'product_name.required' => 'Product name is required.',
            'product_name.max' => 'Product name must not exceed 255 characters.',
            'supplier_name.max' => 'Supplier name must not exceed 255 characters.',
            'unit.max' => 'Unit must not exceed 255 characters.',
            'part_number.max' => 'Part number must not exceed 255 characters.',
        ]);

        try {
            Product::create($validated);

            flash('Item added successfully!')->success();

            return back();
        } catch (QueryException $e) {
            Log::error('ItemsController@store database error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request' => $request->all(),
            ]);

            flash('Database error while saving item. Possible cause: duplicate value, missing column, or invalid table structure.')->error();

            return back()->withInput();
        } catch (Throwable $e) {
            Log::error('ItemsController@store failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            flash('Unexpected error while adding item: '.$e->getMessage())->error();

            return back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ], [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'product_name.required' => 'Product name is required.',
            'product_name.max' => 'Product name must not exceed 255 characters.',
            'supplier_name.max' => 'Supplier name must not exceed 255 characters.',
            'unit.max' => 'Unit must not exceed 255 characters.',
            'part_number.max' => 'Part number must not exceed 255 characters.',
        ]);

        try {
            $product = Product::findOrFail($id);

            $product->update($validated);

            flash('Item updated successfully!')->success();

            return back();
        } catch (ModelNotFoundException $e) {
            Log::warning('ItemsController@update item not found', [
                'product_id' => $id,
                'request' => $request->all(),
            ]);

            flash('Item not found. It may have already been deleted.')->error();

            return back()->withInput();
        } catch (QueryException $e) {
            Log::error('ItemsController@update database error', [
                'product_id' => $id,
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request' => $request->all(),
            ]);

            flash('Database error while updating item. Please check the entered values and database structure.')->error();

            return back()->withInput();
        } catch (Throwable $e) {
            Log::error('ItemsController@update failed', [
                'product_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            flash('Unexpected error while updating item: '.$e->getMessage())->error();

            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            flash('Item deleted successfully!')->success();

            return back();
        } catch (ModelNotFoundException $e) {
            Log::warning('ItemsController@destroy item not found', [
                'product_id' => $id,
            ]);

            flash('Item not found. It may have already been deleted.')->error();

            return back();
        } catch (QueryException $e) {
            Log::error('ItemsController@destroy database error', [
                'product_id' => $id,
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            flash('Cannot delete this item because it is already used in other records.')->error();

            return back();
        } catch (Throwable $e) {
            Log::error('ItemsController@destroy failed', [
                'product_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Unexpected error while deleting item: '.$e->getMessage())->error();

            return back();
        }
    }
}
