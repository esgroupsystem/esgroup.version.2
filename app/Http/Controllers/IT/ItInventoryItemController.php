<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use App\Models\ItInventoryItem;
use Illuminate\Http\Request;

class ItInventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $category = trim((string) $request->category);

        $items = ItInventoryItem::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->orderBy('item_name')
            ->paginate(10)
            ->withQueryString();

        $categories = ItInventoryItem::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('it_department.inventory.index', compact('items', 'search', 'category', 'categories'));
    }

    public function create()
    {
        return view('it_department.inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        ItInventoryItem::create($validated);

        return redirect()
            ->route('it-inventory.index')
            ->with('success', 'IT inventory item added successfully.');
    }

    public function edit($id)
    {
        $item = ItInventoryItem::findOrFail($id);

        return view('it_department.inventory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ItInventoryItem::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock_qty' => 'required|integer|min:0',
        ]);

        $item->update($request->all());

        return redirect()
            ->route('it-inventory.index')
            ->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        $item = ItInventoryItem::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Deleted successfully.');
    }
}
