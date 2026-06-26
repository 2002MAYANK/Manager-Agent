<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitInsight extends Model
{
    protected $fillable = [
        'commit_log_id',
        'feature_category',
        'business_impact',
        'technical_complexity',
        'risk_level',
        'summary',
    ];

    public function commitLog()
    {
        return $this->belongsTo(CommitLog::class, 'commit_log_id');
    }
}
