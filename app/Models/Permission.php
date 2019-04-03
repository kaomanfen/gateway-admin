<?php

namespace App\Models;

class Permission extends BaseModel
{
    protected $fillable = [
        'uid',
        'privilege_id',
        'project_id',
        'role',
        'type',
    ];

    /**
     * 用户
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'uid');
    }

    public static function boot()
    {

    }
}
