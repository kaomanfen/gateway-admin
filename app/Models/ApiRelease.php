<?php
/**
 * Created by PhpStorm.
 * User: caoyang
 * Date: 2017/9/6
 * Time: 下午8:31
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRelease extends Model
{

    protected $fillable = [

        'api_id',
        'project_id',
        'method',
        'path',
        'is_auth',
        'is_sign',
        'request',
        'response_type',
        'response',
        'server_path',
        'network',
        'version',
        'creator',
        'timeout',
        'is_cache',
        'try_times',
        'upstream_url',
    ];
    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];


    public function setTable($env)
    {
        $this->table = 'apis_' . $env;
        return $this;
    }
}
