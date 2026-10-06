<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\PayrollSettingVersion;
use Illuminate\Support\Collection;

/** Payroll Settings versions (one per effective date). */
interface PayrollSettingVersionRepositoryInterface
{
    /** @return Collection<int, PayrollSettingVersion> newest effective date first, with creator / updater */
    public function all(): Collection;

    /**
     * Plain rows for the settings cache: id, label, effective_from (Y-m-d), values.
     *
     * @return list<array{id: int, label: string, effective_from: string, values: array<string, mixed>}> oldest first
     */
    public function snapshot(): array;

    public function find(int $id): ?PayrollSettingVersion;

    public function findOrFail(int $id): PayrollSettingVersion;

    public function existsOn(string $date, ?int $ignoreId = null): bool;

    /** @param  array<string, mixed>  $attributes */
    public function create(array $attributes): PayrollSettingVersion;

    /** @param  array<string, mixed>  $attributes */
    public function update(PayrollSettingVersion $version, array $attributes): PayrollSettingVersion;

    public function delete(PayrollSettingVersion $version): void;
}
