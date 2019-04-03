<?php
/**
 * Created by PhpStorm.
 * User: caoyang
 * Date: 2017/9/5
 * Time: 下午8:14
 */

namespace App\Models;

class Follow extends BaseModel
{
    const UPDATED_AT = null;
    protected $fillable = [
        'api_id',
        'uid',
        'email',
        'created_at',
    ];

    /**
     * 接口
     */
    public function api()
    {
        return $this->hasOne('App\Models\Api', 'api_id', 'id');
    }

    /**
     * 接口
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'uid');
    }

}