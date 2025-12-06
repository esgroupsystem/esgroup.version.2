<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // LIST VIEW
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('maintenance.category.index', compact('categories'));
    }

    // SAVE CATEGORY
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        flash('Category added successfully!')->success();

        return redirect()->back();
    }

    // UPDATE CATEGORY
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'unique:categories,name,'.$id],
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
        ]);

        flash('Category updated successfully!')->success();

        return redirect()->back();
    }

    // DELETE CATEGORY
    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        flash('Category deleted successfully!')->success();

        return redirect()->back();
    }
}
