<?php
/**
 * Created by PhpStorm.
 * User: caoyang
 * Date: 2017/9/5
 * Time: 下午8:14
 */

namespace App\Models;

class OperationLog extends BaseModel
{
    const UPDATED_AT = null;
    protected $fillable = [
        'uid',
        'username',
        'operation_id',
        'content',
        'type',
        'type_id',
        'created_at',
        'data',
    ];
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * 接口
     */
    public function api()
    {
        return $this->hasOne('App\Models\Api', 'api_id', 'id');
    }

    public function getContentAttribute($value)
    {
        if ($this->type == 1) {
            $str = '<a href="/#/group/' . $this->type_id . '/api/detail_' . $this->operation_id . '">' . $value . '</a>';
        } else {
            $str = $value;
        }
        return $str;
    }

}