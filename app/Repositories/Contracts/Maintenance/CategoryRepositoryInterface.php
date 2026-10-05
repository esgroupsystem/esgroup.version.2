<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\Category;
use Illuminate\Support\Collection;

/** Product categories. */
interface CategoryRepositoryInterface
{
    /** @return Collection<int, Category> by name, with products_count */
    public function allWithProductCount(): Collection;

    /** @return Collection<int, Category> by name */
    public function all(): Collection;

    public function findOrFail(int $id): Category;

    public function create(string $name): Category;

    public function update(Category $category, string $name): void;

    public function delete(Category $category): void;
}
