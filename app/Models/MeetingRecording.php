<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingRecording extends Model
{
    protected $fillable = [
        'meeting_id',
        'file_path',
        'file_type',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
