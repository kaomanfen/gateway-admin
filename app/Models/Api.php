<?php
/**
 * Created by PhpStorm.
 * User: caoyang
 * Date: 2017/9/6
 * Time: 下午8:31
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $name = 1;

    protected $fillable = [
        'project_id',
        'category_id',
        'method',
        'name',
        'parent_id',
        'description',
        'path',
        'is_auth',
        'is_sign',
        'request',
        'response_type',
        'response',
        'response_code',
        'response_text',
        'remark',
        'server_path',
        'test_api_id',
        'beta_api_id',
        'prod_api_id',
        'network',
        'version',
        'creator',
        'status',
        'timeout',
        'is_cache',
        'try_times',
        'upstream_url',
    ];
    protected $casts = [
        'request' => 'array',
        'response' => 'array',
        'response_code' => 'array',
        'response_text' => 'array',
    ];

    /**
     * 分类
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category')->withDefault([
            'id' => 0,
            'name' => '默认分类',
        ]);
    }

    /**
     * 分类
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'creator', 'id')->withDefault([
            'id' => 0,
            'name' => '默认用户',
        ]);
    }

    /**
     * 项目
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }

    /**
     * 项目
     */
    public function ratelimitapi()
    {
        return $this->belongsTo('App\Models\RateLimitingApi', 'id', 'api_id');
    }

    /**
     * 关注
     */
    public function follow()
    {
        return $this->hasOne('App\Models\Follow', 'api_id', 'id');
    }

}
