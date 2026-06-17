<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'team_productivity',
        'top_performers',
        'attention_required',
        'risks',
        'full_report',
        'start_date',
        'end_date',
        'generated_at',
    ];

    protected $casts = [
        'top_performers' => 'array',
        'attention_required' => 'array',
        'risks' => 'array',
    ];
}
