<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $employee_id
 * @property string|null $profile_picture
 * @property string|null $sss_number
 * @property string|null $tin_number
 * @property string|null $philhealth_number
 * @property string|null $pagibig_number
 * @property string|null $birth_certificate
 * @property string|null $resume
 * @property string|null $contract
 */
class EmployeeAsset extends Model
{
    protected $fillable = [
        'employee_id',
        'profile_picture',
        'sss_number',
        'tin_number',
        'philhealth_number',
        'pagibig_number',
        'birth_certificate',
        'resume',
        'contract',
    ];

    protected $casts = [
        'sss_updated_at' => 'datetime',
        'tin_updated_at' => 'datetime',
        'philhealth_updated_at' => 'datetime',
        'pagibig_updated_at' => 'datetime',

        'profile_picture_updated_at' => 'datetime',
        'birth_certificate_updated_at' => 'datetime',
        'resume_updated_at' => 'datetime',
        'contract_updated_at' => 'datetime',
    ];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
