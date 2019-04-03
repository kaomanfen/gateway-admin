<?php

namespace App\Models;

class RateLimitingApi extends BaseModel
{

    const UPDATE_AT = null;
    protected $fillable = [
        'rate_id',
        'project_id',
        'api_id',
    ];

    /**
     * 项目
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }

    /**
     * 策略
     */
    public function ratelimit()
    {
        return $this->belongsTo('App\Models\RateLimiting', 'rate_id');
    }

    /**
     * api
     */
    public function api()
    {
        return $this->belongsTo('App\Models\Api', 'api_id', 'prod_api_id');
    }

}
