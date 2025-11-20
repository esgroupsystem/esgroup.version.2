<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'status',
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
    ];

    public function jobOrdersAssigned()
    {
        return $this->hasMany(JobOrder::class, 'job_assign_person');
    }

    public function jobOrdersCreated()
    {
        return $this->hasMany(JobOrder::class, 'created_by');
    }
}
