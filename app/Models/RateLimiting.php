<?php

namespace App\Models;

class RateLimiting extends BaseModel
{

    protected $fillable = [
        'name',
        'period',
        'api_limit',
        'user_limit',
        'smooth_limit',
        'burst',
    ];

}
