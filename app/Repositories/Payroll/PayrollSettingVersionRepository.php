<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\PayrollSettingVersion;
use App\Repositories\Contracts\Payroll\PayrollSettingVersionRepositoryInterface;
use Illuminate\Support\Collection;

final class PayrollSettingVersionRepository implements PayrollSettingVersionRepositoryInterface
{
    public function all(): Collection
    {
        return PayrollSettingVersion::query()
            ->with(['creator:id,full_name,username', 'updater:id,full_name,username'])
            ->orderByDesc('effective_from')
            ->get();
    }

    public function snapshot(): array
    {
        return PayrollSettingVersion::query()
            ->orderBy('effective_from')
            ->get(['id', 'label', 'effective_from', 'values'])
            ->map(fn (PayrollSettingVersion $version): array => [
                'id' => (int) $version->id,
                'label' => (string) $version->label,
                'effective_from' => $version->effective_from->toDateString(),
                'values' => (array) $version->values,
            ])
            ->values()
            ->all();
    }

    public function find(int $id): ?PayrollSettingVersion
    {
        return PayrollSettingVersion::query()->find($id);
    }

    public function findOrFail(int $id): PayrollSettingVersion
    {
        return PayrollSettingVersion::query()->findOrFail($id);
    }

    public function existsOn(string $date, ?int $ignoreId = null): bool
    {
        return PayrollSettingVersion::query()
            ->whereDate('effective_from', $date)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function create(array $attributes): PayrollSettingVersion
    {
        return PayrollSettingVersion::query()->create($attributes);
    }

    public function update(PayrollSettingVersion $version, array $attributes): PayrollSettingVersion
    {
        $version->update($attributes);

        return $version;
    }

    public function delete(PayrollSettingVersion $version): void
    {
        $version->delete();
    }
}
