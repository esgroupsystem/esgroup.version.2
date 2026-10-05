<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\HrOffense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** The company code of conduct (HR Offenses). */
interface HrOffenseRepositoryInterface
{
    /**
     * Search on section / description, exact type and gravity, optional id. By id.
     *
     * @return LengthAwarePaginator<int, HrOffense>
     */
    public function paginate(string $search, string $type, string $gravity, ?int $id, int $perPage = 10): LengthAwarePaginator;

    /** @return Collection<int, HrOffense> by section */
    public function allBySection(): Collection;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): HrOffense;
}
