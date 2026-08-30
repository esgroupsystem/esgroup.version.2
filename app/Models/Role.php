<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $name
 */
class Role extends Model
{
    protected $fillable = ['name'];
}
