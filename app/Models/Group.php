<?php

namespace App\Models;

class Group extends BaseModel
{

    protected $fillable = [
        'name',
        'description',
        'privilege',
    ];

    /**
     * 分类
     */
    public function projects()
    {
        return $this->hasMany('App\Models\Project', 'group_id', 'id');
    }


}
