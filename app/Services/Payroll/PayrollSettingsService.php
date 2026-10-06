<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\PayrollSettingVersion;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollSettingVersionRepositoryInterface;
use App\Support\Payroll\PayrollSettingCatalog;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Payroll Settings: every rate the payroll engine uses, stored by "effective from" date.
 *
 * The engine still reads `config('payroll.*')` / `config('sss.*')`; this service writes the
 * values of the right version into that config. The current version is applied on every
 * request (AppServiceProvider); a payroll run applies the version of its period start
 * through using(), and the simulator applies unsaved values through usingValues().
 */
final class PayrollSettingsService
{
    private const CACHE_KEY = 'payroll_settings.versions.v1';

    /** @var array{id: int|null, label: string, effective_from: string|null, values: array<string, mixed>}|null */
    private ?array $active = null;

    public function __construct(
        private readonly PayrollSettingVersionRepositoryInterface $versions,
        private readonly PayrollRepositoryInterface $payrolls,
    ) {}

    /**
     * The version in effect on a date (newest effective_from on or before it).
     *
     * @return array{id: int|null, label: string, effective_from: string|null, values: array<string, mixed>}
     */
    public function versionFor(CarbonInterface|string|null $date = null): array
    {
        $day = $this->day($date);
        $match = null;

        foreach ($this->snapshot() as $version) {
            if ($version['effective_from'] <= $day) {
                $match = $version;
            }
        }

        if ($match === null) {
            return ['id' => null, 'label' => 'Built-in starting values', 'effective_from' => null, 'values' => PayrollSettingCatalog::defaults()];
        }

        return ['values' => PayrollSettingCatalog::normalize($match['values'])] + $match;
    }

    /** Apply the version in effect on a date to the runtime config. */
    public function apply(CarbonInterface|string|null $date = null): void
    {
        $version = $this->versionFor($date);
        $this->applyValues($version['values']);
        $this->active = $version;
    }

    /**
     * Run a callback with the settings of a date, then put the previous config back.
     *
     * @template T
     *
     * @param  callable(array{id: int|null, label: string, effective_from: string|null, values: array<string, mixed>}): T  $callback
     * @return T
     */
    public function using(CarbonInterface|string|null $date, callable $callback): mixed
    {
        return $this->runWith($this->versionFor($date), $callback);
    }

    /**
     * Run a callback with one saved version, whatever its date (the simulator's "compare with").
     *
     * @template T
     *
     * @param  callable(array{id: int|null, label: string, effective_from: string|null, values: array<string, mixed>}): T  $callback
     * @return T
     */
    public function usingVersion(int $versionId, callable $callback): mixed
    {
        $match = collect($this->snapshot())->firstWhere('id', $versionId);

        if ($match === null) {
            throw ValidationException::withMessages(['compare_version_id' => 'That settings version no longer exists.']);
        }

        return $this->runWith(['values' => PayrollSettingCatalog::normalize($match['values'])] + $match, $callback);
    }

    /**
     * Run a callback with unsaved values (the simulator), then put the previous config back.
     *
     * @template T
     *
     * @param  array<string, mixed>  $values
     * @param  callable(array{id: int|null, label: string, effective_from: string|null, values: array<string, mixed>}): T  $callback
     * @return T
     */
    public function usingValues(array $values, string $label, callable $callback): mixed
    {
        return $this->runWith([
            'id' => null,
            'label' => $label,
            'effective_from' => null,
            'values' => PayrollSettingCatalog::normalize($values),
        ], $callback);
    }

    /**
     * The version applied right now, without its values (stored on each payroll and item).
     *
     * @return array{version_id: int|null, label: string, effective_from: string|null}
     */
    public function activeSummary(): array
    {
        $active = $this->active ?? $this->versionFor(null);

        return [
            'version_id' => $active['id'],
            'label' => $active['label'],
            'effective_from' => $active['effective_from'],
        ];
    }

    /** @return array<string, mixed> values applied right now */
    public function activeValues(): array
    {
        return ($this->active ?? $this->versionFor(null))['values'];
    }

    /** @return Collection<int, PayrollSettingVersion> */
    public function list(): Collection
    {
        return $this->versions->all();
    }

    /** @return array<int, array{total: int, finalized: int}> */
    public function usage(): array
    {
        return $this->payrolls->settingsVersionUsage();
    }

    public function findOrFail(int $id): PayrollSettingVersion
    {
        return $this->versions->findOrFail($id);
    }

    /** @param  array{effective_from: string, label: string, notes?: string|null, values: array<string, mixed>}  $data */
    public function create(array $data, ?int $userId): PayrollSettingVersion
    {
        $this->assertDateFree($data['effective_from']);

        $version = $this->versions->create([
            'effective_from' => $data['effective_from'],
            'label' => $data['label'],
            'notes' => $data['notes'] ?? null,
            'values' => PayrollSettingCatalog::normalize($data['values']),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->forget();

        return $version;
    }

    /** @param  array{effective_from: string, label: string, notes?: string|null, values: array<string, mixed>}  $data */
    public function update(PayrollSettingVersion $version, array $data, ?int $userId): PayrollSettingVersion
    {
        $this->assertDateFree($data['effective_from'], (int) $version->id);

        $this->versions->update($version, [
            'effective_from' => $data['effective_from'],
            'label' => $data['label'],
            'notes' => $data['notes'] ?? null,
            'values' => PayrollSettingCatalog::normalize($data['values']),
            'updated_by' => $userId,
        ]);

        $this->forget();

        return $version;
    }

    public function delete(PayrollSettingVersion $version): void
    {
        if (count($this->versions->snapshot()) <= 1) {
            throw ValidationException::withMessages([
                'version' => 'Keep at least one settings version. Edit it instead of deleting it.',
            ]);
        }

        $this->versions->delete($version);
        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array{id: int|null, label: string, effective_from: string|null, values: array<string, mixed>}  $version
     */
    private function runWith(array $version, callable $callback): mixed
    {
        $config = ['payroll' => config('payroll'), 'sss' => config('sss')];
        $previous = $this->active;

        try {
            $this->applyValues($version['values']);
            $this->active = $version;

            return $callback($version);
        } finally {
            config($config);
            $this->active = $previous;
        }
    }

    /** @param  array<string, mixed>  $values */
    private function applyValues(array $values): void
    {
        config(PayrollSettingCatalog::toConfig(PayrollSettingCatalog::normalize($values)));
    }

    /** @return list<array{id: int, label: string, effective_from: string, values: array<string, mixed>}> */
    private function snapshot(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->versions->snapshot());
    }

    private function assertDateFree(string $date, ?int $ignoreId = null): void
    {
        if ($this->versions->existsOn($date, $ignoreId)) {
            throw ValidationException::withMessages([
                'effective_from' => 'Another settings version already starts on this date. Edit that one instead.',
            ]);
        }
    }

    private function day(CarbonInterface|string|null $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->toDateString();
        }

        return Carbon::parse($date ?? 'now', 'Asia/Manila')->toDateString();
    }
}
