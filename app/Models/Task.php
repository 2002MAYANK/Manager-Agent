<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'status',
        'assigned_date',
        'due_date',
        'completed_date',
    ];

    public function employee()
{
    return $this->belongsTo(Employee::class);
}
}
