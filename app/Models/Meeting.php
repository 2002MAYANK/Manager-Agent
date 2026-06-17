<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'title',
        'notes',
        'meeting_date',
        'total_participants',
        'total_transcript_entries',
        'most_active_speaker',
        'least_active_speaker',
        'meeting_duration',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

    public function transcripts()
    {
        return $this->hasMany(MeetingTranscript::class);
    }

    public function recordings()
    {
        return $this->hasMany(MeetingRecording::class);
    }
}
