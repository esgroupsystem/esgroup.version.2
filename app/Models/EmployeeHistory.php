<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHistory extends Model
{
    protected $fillable = ['employee_id','title','description','start_date','end_date'];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
