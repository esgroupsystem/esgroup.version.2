<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\Category;
use App\Repositories\Contracts\Maintenance\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public function allWithProductCount(): Collection
    {
        return Category::query()->withCount('products')->orderBy('name')->get();
    }

    public function all(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    public function findOrFail(int $id): Category
    {
        return Category::query()->findOrFail($id);
    }

    public function create(string $name): Category
    {
        return Category::query()->create(['name' => $name]);
    }

    public function update(Category $category, string $name): void
    {
        $category->update(['name' => $name]);
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
