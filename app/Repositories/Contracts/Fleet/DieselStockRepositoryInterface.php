<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Fleet;

use App\Models\DieselStock;
use Illuminate\Support\Collection;

/** Diesel stock movements (`diesel_stocks`): in, out and adjustment, in liters. */
interface DieselStockRepositoryInterface
{
    /** Liters of one movement type, all time or within $from..$to. */
    public function liters(string $type, ?string $from = null, ?string $to = null): float;

    /**
     * Movements dated $from..$to, newest first, with bus and encoder. With a bus, also the
     * stock-only rows (no bus).
     *
     * @return Collection<int, DieselStock>
     */
    public function movements(?int $busId, string $from, string $to): Collection;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): DieselStock;

    /** Updates the automatic "out" row of an odometer reading (reference `ODO-<id>`). */
    public function updateOdometerDeduction(int $submissionId, float $liters, string $date): void;

    /** Deletes the automatic "out" row of an odometer reading. */
    public function deleteOdometerDeduction(int $submissionId, ?int $busId): void;
}
