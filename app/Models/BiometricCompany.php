<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $name
 * @property bool|null $is_active
 * @property string|null $remarks
 */
class BiometricCompany extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return HasMany<EmployeeBiometric, $this> */
    public function employeeBiometrics(): HasMany
    {
        return $this->hasMany(EmployeeBiometric::class);
    }

    /** @return Builder<BiometricCompany> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
