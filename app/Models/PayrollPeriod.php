<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }
}
