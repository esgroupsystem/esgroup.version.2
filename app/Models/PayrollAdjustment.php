<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'payroll_entry_id',
        'type',
        'label',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollEntry()
    {
        return $this->belongsTo(PayrollEntry::class);
    }
}
