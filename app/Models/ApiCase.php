<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCase extends Model
{
    protected $attributes = [
        'server_ip' => '', //后端服务ip默认为空
        'status' => '0', //状态
    ];
    protected $fillable = [
        'api_id',
        'project_id',
        'version',
        'path',
        'name',
        'collect_id',
        'env',
        'method',
        'request',
        'status',
        'error',
        'response',
        'backend_name',
        'server_ip'
    ];
    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    /**
     * 关注
     */
    public function api()
    {
        return $this->hasOne('App\Models\Api', 'id', 'api_id');
    }
}
