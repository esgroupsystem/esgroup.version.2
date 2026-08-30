<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $jo_no
 * @property string|null $bus_no
 * @property string|null $reported_by
 * @property string|null $issue_type
 * @property string|null $cctv_part
 * @property string|null $problem_details
 * @property string|null $action_taken
 * @property string|null $status
 * @property string|null $assigned_to
 * @property string|null $created_by
 * @property \Carbon\CarbonInterface|null $fixed_at
 */
class CctvConcern extends Model
{
    use HasFactory;

    protected $table = 'cctv_job_orders';

    protected $fillable = [
        'jo_no',
        'bus_no',
        'reported_by',
        'issue_type',
        'cctv_part',
        'problem_details',
        'action_taken',
        'status',
        'assigned_to',
        'created_by',
        'fixed_at',
    ];

    protected $casts = [
        'fixed_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<CctvConcernItem, $this> */
    public function usedItems(): HasMany
    {
        return $this->hasMany(CctvConcernItem::class, 'cctv_concern_id');
    }

    /** @return BelongsTo<BusDetail, $this> */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(BusDetail::class, 'bus_no');
    }
}
