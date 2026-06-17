<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitLog extends Model
{
    protected $fillable = [
        'employee_id',
        'commit_hash',
        'commit_message',
        'lines_added',
        'lines_deleted',
        'commit_date',
    ];

    public function employee()
{
    return $this->belongsTo(Employee::class);
}
}
