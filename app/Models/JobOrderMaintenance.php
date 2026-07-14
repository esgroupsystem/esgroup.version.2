<?php

namespace App\Models;

use App\Enums\JobOrderRepairType;
use App\Enums\JobOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class JobOrderMaintenance extends Model
{
    use SoftDeletes;

    protected $table = 'job_orders_maintenance';

    protected $fillable = [
        'job_order_no',
        'bus_id',
        'bus_no_snapshot',
        'plate_no_snapshot',
        'company_snapshot',
        'garage_snapshot',
        'full_name',
        'mechanic_names',
        'repair_types',
        'description_of_work',
        'odometer_reading',
        'last_odometer_reading',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobOrderStatus::class,
            'mechanic_names' => 'array',
            'repair_types' => 'array',
            'odometer_reading' => 'integer',
            'last_odometer_reading' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(JobOrderMaintenanceHistory::class, 'job_order_maintenance_id')
            ->latest();
    }

    public function statusPeriods(): HasMany
    {
        return $this->hasMany(JobOrderMaintenanceStatusPeriod::class, 'job_order_maintenance_id')
            ->orderBy('started_at');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('job_order_no', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->orWhere('description_of_work', 'like', "%{$search}%")
                ->orWhere('bus_no_snapshot', 'like', "%{$search}%")
                ->orWhere('plate_no_snapshot', 'like', "%{$search}%")
                ->orWhere('company_snapshot', 'like', "%{$search}%")
                ->orWhere('garage_snapshot', 'like', "%{$search}%")
                ->orWhereJsonContains('mechanic_names', $search)
                ->orWhereHas('bus', function (Builder $busQuery) use ($search): void {
                    $busQuery->where('bus_no', 'like', "%{$search}%")
                        ->orWhere('plate_no', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%");
                });
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status?->badgeClass() ?? 'badge-subtle-secondary text-secondary';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->status?->icon() ?? 'fas fa-circle-question';
    }

    public function getStatusDescriptionAttribute(): string
    {
        return $this->status?->description() ?? 'No status information available.';
    }

    public function getOdometerDifferenceAttribute(): ?int
    {
        if ($this->odometer_reading === null || $this->last_odometer_reading === null) {
            return null;
        }

        return $this->odometer_reading - $this->last_odometer_reading;
    }

    public function getIsOdometerLowerThanLastAttribute(): bool
    {
        return $this->odometer_difference !== null && $this->odometer_difference < 0;
    }

    public function getOdometerComparisonLabelAttribute(): string
    {
        if ($this->odometer_reading === null) {
            return 'No current odometer reading encoded.';
        }

        if ($this->last_odometer_reading === null) {
            return 'No previous maintenance reading available.';
        }

        if ($this->is_odometer_lower_than_last) {
            return 'Current reading is lower than the previous reading.';
        }

        return number_format($this->odometer_difference).' km since the previous maintenance reading.';
    }

    public function getMechanicNamesListAttribute(): array
    {
        return collect($this->mechanic_names ?? [])
            ->map(fn ($name): string => trim((string) $name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values()
            ->all();
    }

    public function getMechanicNamesLabelAttribute(): string
    {
        return $this->mechanic_names_list === []
            ? 'Not assigned'
            : implode(', ', $this->mechanic_names_list);
    }

    public function getRepairTypeEnumsAttribute(): Collection
    {
        return collect($this->repair_types ?? [])
            ->map(fn ($value): ?JobOrderRepairType => JobOrderRepairType::tryFrom((string) $value))
            ->filter()
            ->values();
    }

    public function getRepairTypesLabelAttribute(): string
    {
        $labels = $this->repair_type_enums
            ->map(fn (JobOrderRepairType $type): string => $type->label())
            ->all();

        return $labels === [] ? 'Not encoded' : implode(', ', $labels);
    }

    public function downtimeMinutes(?JobOrderStatus $status = null): int
    {
        $periods = $this->relationLoaded('statusPeriods')
            ? $this->statusPeriods
            : $this->statusPeriods()->get();

        return $periods
            ->filter(function (JobOrderMaintenanceStatusPeriod $period) use ($status): bool {
                if (! $period->status?->countsAsDowntime()) {
                    return false;
                }

                return $status === null || $period->status === $status;
            })
            ->sum(fn (JobOrderMaintenanceStatusPeriod $period): int => $period->duration_minutes);
    }

    public function getTotalDowntimeMinutesAttribute(): int
    {
        return $this->downtimeMinutes();
    }

    public function getTotalDowntimeLabelAttribute(): string
    {
        return self::formatDurationMinutes($this->total_downtime_minutes);
    }

    public function getDowntimeBreakdownAttribute(): array
    {
        return collect(JobOrderStatus::downtimeStatuses())
            ->mapWithKeys(function (JobOrderStatus $status): array {
                $minutes = $this->downtimeMinutes($status);

                return [
                    $status->value => [
                        'status' => $status,
                        'minutes' => $minutes,
                        'label' => self::formatDurationMinutes($minutes),
                    ],
                ];
            })
            ->all();
    }

    public function getIsDowntimeRunningAttribute(): bool
    {
        return $this->status?->countsAsDowntime() ?? false;
    }

    public static function formatDurationMinutes(int $minutes): string
    {
        $minutes = max($minutes, 0);
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' '.str('day')->plural($days);
        }

        if ($hours > 0) {
            $parts[] = $hours.' '.str('hour')->plural($hours);
        }

        if ($remainingMinutes > 0 || $parts === []) {
            $parts[] = $remainingMinutes.' '.str('minute')->plural($remainingMinutes);
        }

        return implode(' ', $parts);
    }
}
