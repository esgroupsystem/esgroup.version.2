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
    public static function availableAssignableRoles(?self $actor, ?self $target = null): array
    {
        $roleNames = \Spatie\Permission\Models\Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $isDeveloper = $actor?->hasRole('Developer') === true;
        $targetIsDeveloper = $target?->hasRole('Developer') === true;

        if (! $isDeveloper) {
            $roleNames = array_values(array_filter(
                $roleNames,
                static fn (string $name): bool => $name !== 'Developer'
            ));
        }

        if ($targetIsDeveloper && ! $isDeveloper) {
            return ['Developer'];
        }

        return array_values(array_unique($roleNames));
    }

    public function isDeveloper(): bool
    {
        return $this->hasRole('Developer');
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
