<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendence extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'present',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
