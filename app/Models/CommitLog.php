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
        'repository_name',
        'project_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function insight()
    {
        return $this->hasOne(CommitInsight::class, 'commit_log_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
