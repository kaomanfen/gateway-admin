<?php

namespace App\Models;


class CollectSet extends BaseModel
{
    protected $attributes = [
        'description' => '', //默认值
    ];
    protected $fillable = [
        'project_id',
        'name',
        'description',

    ];

    /**
     * 分类
     */
    public function collects()
    {
        return $this->hasMany('App\Models\ApiCase', 'collect_id', 'id');
    }
}
