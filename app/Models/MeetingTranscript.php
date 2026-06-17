<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingTranscript extends Model
{
    protected $fillable = [
        'meeting_id',
        'employee_id',
        'speaker_name',
        'spoken_text',
        'timestamp',
        'sequence',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
