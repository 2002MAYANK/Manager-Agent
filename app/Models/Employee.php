<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'department',
        'designation',
        'team_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendence::class);
    }

    public function commits()
    {
        return $this->hasMany(CommitLog::class);
    }

    public function meetings()
    {
        return $this->belongsToMany(Meeting::class);
    }
}
