<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreCategoryRequest;
use App\Http\Requests\Maintenance\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Maintenance\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(): View
    {
        return view('maintenance.category.index', [
            'categories' => Category::query()->orderBy('name')->get(),
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
