<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreCategoryRequest;
use App\Http\Requests\Maintenance\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Maintenance\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('products/categories/index', [
            'categories' => Category::query()->withCount('products')->orderBy('name')->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'products_count' => (int) $category->products_count,
                    'update_url' => route('category.update', $category->id),
                    'destroy_url' => route('category.destroy', $category->id),
                ])->values(),
            'can' => [
                'create' => (bool) $user?->can('category.create'),
                'update' => (bool) $user?->can('category.update'),
                'delete' => (bool) $user?->can('category.delete'),
            ],
            'urls' => ['store' => route('category.store')],
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated('name'));
        flash('Category added successfully!')->success();

        return back();
    }

    public function update(UpdateCategoryRequest $request, int $id): RedirectResponse
    {
        $this->categoryService->update(Category::query()->findOrFail($id), $request->validated('name'));
        flash('Category updated successfully!')->success();

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->categoryService->delete(Category::query()->findOrFail($id));
        flash('Category deleted successfully!')->success();

        return back();
    }
}
