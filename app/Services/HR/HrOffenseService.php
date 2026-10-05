<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\HrOffense;
use App\Repositories\Contracts\HR\HrOffenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** HR Offenses: the code of conduct used by violation histories. */
final class HrOffenseService
{
    public function __construct(
        private readonly HrOffenseRepositoryInterface $offenses,
    ) {}

    /** Unknown type / gravity filters are ignored. */
    public function normalizeFilter(mixed $value, array $allowed): string
    {
        return in_array($value, $allowed, true) ? (string) $value : '';
    }

    /** @return LengthAwarePaginator<int, HrOffense> */
    public function paginate(string $search, string $type, string $gravity, ?int $id): LengthAwarePaginator
    {
        return $this->offenses->paginate($search, $type, $gravity, $id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): HrOffense
    {
        return $this->offenses->create($data);
    }
}
