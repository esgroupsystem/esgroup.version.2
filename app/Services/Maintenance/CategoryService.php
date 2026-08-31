<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\Category;

final class CategoryService
{
    public function create(string $name): Category
    {
        return Category::query()->create(['name' => trim($name)]);
    }

    public function update(Category $category, string $name): Category
    {
        $category->update(['name' => trim($name)]);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
