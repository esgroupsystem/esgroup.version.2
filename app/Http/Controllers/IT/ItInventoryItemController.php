<?php

declare(strict_types=1);

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use App\Http\Requests\ITDepartment\StoreItInventoryItemRequest;
use App\Http\Requests\ITDepartment\UpdateItInventoryItemRequest;
use App\Models\ItInventoryItem;
use App\Services\IT\ItInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ItInventoryItemController extends Controller
{
    public function __construct(
        private readonly ItInventoryService $inventoryService,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));

        return view('it_department.inventory.index', [
            'items' => $this->inventoryService->paginate($search, $category),
            'search' => $search,
            'category' => $category,
            'categories' => $this->inventoryService->categories(),
        ]);
    }

    public function create(): View
    {
        return view('it_department.inventory.create');
    }

    public function store(StoreItInventoryItemRequest $request): RedirectResponse
    {
        $this->inventoryService->create($request->validated());

        return redirect()
            ->route('it-inventory.index')
            ->with('success', 'IT inventory item added successfully.');
    }

    public function edit(int $id): View
    {
        return view('it_department.inventory.edit', [
            'item' => ItInventoryItem::query()->findOrFail($id),
        ]);
    }

    public function update(UpdateItInventoryItemRequest $request, int $id): RedirectResponse
    {
        $item = ItInventoryItem::query()->findOrFail($id);
        $this->inventoryService->update($item, $request->validated());

        return redirect()
            ->route('it-inventory.index')
            ->with('success', 'Updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->inventoryService->delete(ItInventoryItem::query()->findOrFail($id));

        return back()->with('success', 'Deleted successfully.');
    }
}
