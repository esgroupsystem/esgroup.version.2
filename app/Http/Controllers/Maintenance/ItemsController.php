<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::with('category')->orderBy('product_name')->get();

        return view('maintenance.items.index', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'product_name'  => 'required',
            'unit'          => 'nullable',
            'part_number'   => 'nullable',
            'details'       => 'nullable',
        ]);

        Product::create($request->all());

        flash('Item added successfully!')->success();
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'product_name'  => 'required',
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());

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
