<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = [
        'name',
        'token',
        'is_active',
        'request_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'request_count' => 'integer',
        'last_used_at' => 'datetime',
    ];
}
