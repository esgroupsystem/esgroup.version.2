<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->orderBy('product_name')
            ->paginate(10);

        return view('stock.index', compact('products'));
    }
}
