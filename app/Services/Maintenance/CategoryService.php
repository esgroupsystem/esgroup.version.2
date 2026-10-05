<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\Category;
use App\Repositories\Contracts\Maintenance\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

/** Product categories (Products → Categories). */
final class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    /** @return Collection<int, Category> by name, with products_count */
    public function list(): Collection
    {
        return $this->categories->allWithProductCount();
    }

    public function create(string $name): Category
    {
        return $this->categories->create(trim($name));
    }

    public function update(int $id, string $name): Category
    {
        $category = $this->categories->findOrFail($id);
        $this->categories->update($category, trim($name));

        return $category;
    }

    public function delete(int $id): void
    {
        $this->categories->delete($this->categories->findOrFail($id));
    }
}
