<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttachment extends Model
{
    protected $fillable = ['employee_id','file_name','file_path','mime_type','size'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
