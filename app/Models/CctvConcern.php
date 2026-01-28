<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
