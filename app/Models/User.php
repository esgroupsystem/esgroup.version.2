<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string|null $name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $password
 * @property string|null $full_name
 * @property string|null $role
 * @property string|null $status
 * @property string|null $location_id
 * @property string|null $account_status
 * @property \Carbon\CarbonInterface|null $last_online
 * @property \Carbon\CarbonInterface|null $last_out
 * @property bool|null $must_change_password
 * @property-read mixed $role_name
 */
class User extends Authenticatable
{
    /** System role with every permission; hidden from Roles and never assignable from the app. */
    public const DEVELOPER_ROLE = 'Developer';

    use HasApiTokens,
        HasFactory,
        HasRoles,
        Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'status',
        'location_id',
        'account_status',
        'last_online',
        'last_out',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_online' => 'datetime',
        'last_out' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    /**
     * Return roles the actor may assign. Developer is a privileged bootstrap role
     * and may only be assigned or edited by another Developer.
     *
     * @return array<int, string>
     */
    /**
     * Roles the Users page may assign. The Developer role is never offered: it is a system role
     * given only from the command line (php artisan security:make-developer). A Developer account
     * keeps its role, so for that target the only option is Developer itself.
     *
     * @return list<string>
     */
    public static function availableAssignableRoles(?self $actor, ?self $target = null): array
    {
        if ($target?->isDeveloper() === true) {
            return [self::DEVELOPER_ROLE];
        }

        return \Spatie\Permission\Models\Role::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(name) <> ?', [strtolower(self::DEVELOPER_ROLE)])
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    /** Developers hold every permission, old and new (Gate::before in AppServiceProvider). */
    public function isDeveloper(): bool
    {
        return $this->hasRole(self::DEVELOPER_ROLE);
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return HasMany<JobOrder, $this> */
    public function jobOrdersAssigned(): HasMany
    {
        return $this->hasMany(
            JobOrder::class,
            'job_assign_person'
        );
    }

    /** @return HasMany<JobOrder, $this> */
    public function jobOrdersCreated(): HasMany
    {
        return $this->hasMany(
            JobOrder::class,
            'created_by'
        );
    }

    public function getRoleNameAttribute()
    {
        return $this->getRoleNames()->first();
    }
}
