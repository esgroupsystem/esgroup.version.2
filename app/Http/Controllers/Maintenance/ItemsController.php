<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $items = Product::with('category')
            ->orderBy('product_name')
            ->paginate(10, ['*'], 'items_page');

        $stock = Product::with('category')
            ->orderBy('product_name')
            ->paginate(10, ['*'], 'stock_page');

        if ($request->ajax()) {
            return view('maintenance.items.stock_table', ['products' => $stock])->render();
        }

        return view('maintenance.items.index', compact('categories', 'items', 'stock'));
    }

    public function dashboard()
    {
        $productsWithStock = Product::with('category')
            ->where('stock_qty', '>', 0)
            ->orderBy('stock_qty', 'desc')
            ->paginate(10, ['*'], 'with_stock');

        $productsLowStock = Product::with('category')
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty', 'asc')
            ->paginate(10, ['*'], 'low_stock');

        $totalItems = Product::count();
        $totalStock = Product::sum('stock_qty');
        $lowStock = Product::where('stock_qty', '>', 0)->where('stock_qty', '<=', 5)->count();
        $outOfStock = Product::where('stock_qty', '<=', 0)->count();

        return view('maintenance.items.dashboard', compact(
            'productsWithStock',
            'productsLowStock',
            'totalItems',
            'totalStock',
            'lowStock',
            'outOfStock'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ]);

        Product::create([
            'category_id' => $request->category_id,
            'product_name' => $request->product_name,
            'unit' => $request->unit,
            'part_number' => $request->part_number,
            'details' => $request->details,
        ]);

        flash('Item added successfully!')->success();

        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'category_id' => $request->category_id,
            'product_name' => $request->product_name,
            'unit' => $request->unit,
            'part_number' => $request->part_number,
            'details' => $request->details,
        ]);

        flash('Item updated successfully!')->success();

        return back();
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        flash('Item deleted successfully!')->success();

        return back();
    }
}
