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
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class ItInventoryItemController extends Controller
{
    public function __construct(
        private readonly ItInventoryService $inventoryService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));
        $user = $request->user();

        return Inertia::render('it/inventory/index', [
            'items' => $this->inventoryService->paginate($search, $category)
                ->through(fn (ItInventoryItem $item): array => [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'description' => $item->description ? Str::limit($item->description, 65) : null,
                    'category' => $item->category ?: 'Uncategorized',
                    'brand' => $item->brand,
                    'model' => $item->model,
                    'part_number' => $item->part_number,
                    'stock_qty' => (int) $item->stock_qty,
                    'minimum_stock' => (int) $item->minimum_stock,
                    'unit' => $item->unit,
                    'location' => $item->location,
                    'is_active' => (bool) $item->is_active,
                    'edit_url' => route('it-inventory.edit', $item->id),
                    'destroy_url' => route('it-inventory.destroy', $item->id),
                ]),
            'filters' => ['search' => $search, 'category' => $category],
            'categories' => collect($this->inventoryService->categories())->values(),
            'can' => [
                'create' => (bool) $user?->can('it-inventory.create'),
                'update' => (bool) $user?->can('it-inventory.update'),
                'delete' => (bool) $user?->can('it-inventory.delete'),
            ],
            'urls' => [
                'index' => route('it-inventory.index'),
                'create' => route('it-inventory.create'),
            ],
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(StoreItInventoryItemRequest $request): RedirectResponse
    {
        $this->inventoryService->create($request->validated());

        return redirect()
            ->route('it-inventory.index')
            ->with('success', 'IT inventory item added successfully.');
    }

    public function edit(int $id): Response
    {
        return $this->form(ItInventoryItem::query()->findOrFail($id));
    }

    private function form(?ItInventoryItem $item): Response
    {
        return Inertia::render('it/inventory/form', [
            'item' => $item ? ['id' => $item->id, 'item_name' => $item->item_name] : null,
            'values' => [
                'item_name' => (string) ($item?->item_name ?? ''),
                'category' => (string) ($item?->category ?? ''),
                'unit' => (string) ($item?->unit ?? 'pcs'),
                'brand' => (string) ($item?->brand ?? ''),
                'model' => (string) ($item?->model ?? ''),
                'part_number' => (string) ($item?->part_number ?? ''),
                'location' => (string) ($item?->location ?? ''),
                'stock_qty' => (string) ($item?->stock_qty ?? 0),
                'minimum_stock' => (string) ($item?->minimum_stock ?? 0),
                'is_active' => $item ? (bool) $item->is_active : true,
                'description' => (string) ($item?->description ?? ''),
            ],
            'categories' => collect($this->inventoryService->categories())->values(),
            'urls' => [
                'index' => route('it-inventory.index'),
                'submit' => $item ? route('it-inventory.update', $item->id) : route('it-inventory.store'),
            ],
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
