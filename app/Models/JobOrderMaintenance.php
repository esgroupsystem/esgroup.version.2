<?php

namespace App\Models;

use App\Enums\JobOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'description_of_work',
        'odometer_reading',
        'last_odometer_reading',
        'odometer_difference',
        'is_odometer_lower_than_last',
        'status',
        'created_by',
    ];

    protected $casts = [
        'odometer_reading' => 'integer',
        'last_odometer_reading' => 'integer',
        'odometer_difference' => 'integer',
        'is_odometer_lower_than_last' => 'boolean',
        'status' => JobOrderStatus::class,
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('job_order_no', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('description_of_work', 'like', "%{$search}%")
                    ->orWhereHas('bus', function (Builder $query) use ($search) {
                        $query->where('bus_no', 'like', "%{$search}%")
                            ->orWhere('plate_no', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%")
                            ->orWhere('garage', 'like', "%{$search}%");
                    });
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
        return $this->status?->icon() ?? 'fas fa-question-circle';
    }

    public function getStatusDescriptionAttribute(): string
    {
        return $this->status?->description() ?? 'No status description available.';
    }

    public function getOdometerComparisonLabelAttribute(): string
    {
        if ($this->odometer_reading === null) {
            return 'No odometer reading encoded';
        }

        if ($this->last_odometer_reading === null) {
            return 'No previous odometer record';
        }

        if ($this->is_odometer_lower_than_last) {
            return 'Current reading is lower than last reading';
        }

        return number_format($this->odometer_difference).' km difference from last reading';
    }
}
