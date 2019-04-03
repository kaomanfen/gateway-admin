<?php

namespace App\Models;

use App\Services\LogService;
use Illuminate\Support\Facades\Auth;

class Project extends BaseModel
{

    protected $fillable = [
        'name',
        'group_id',
        'desc',
        'backend_name',
        'base_path',
        'product_line',
        'test_servers',
        'beta_servers',
        'prod_servers',
        'project_type',
        'status',
        'ding_robot'
    ];

    public function getTestServersAttribute($value)
    {
        return json_decode($value, 1);
    }

    public function setTestServersAttribute($value)
    {
        $this->attributes['test_servers'] = json_encode($value);
    }

    /**
     * Beta环境
     * @param $value
     * @return mixed
     */
    public function getBetaServersAttribute($value)
    {
        return json_decode($value, 1);
    }

    public function setBetaServersAttribute($value)
    {
        $this->attributes['beta_servers'] = json_encode($value);
    }

    public function getProdServersAttribute($value)
    {
        return json_decode($value, 1);
    }

    public function setProdServersAttribute($value)
    {
        $this->attributes['prod_servers'] = json_encode($value);
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        Project::created(function ($model) {
            $message = app(LogService::class);
            //根据用户id 获取用户名称
            $user = Auth::user();
            $attributes = [
                'type_id' => $model->id,
                'type' => 4,
                'operation_id' => $model->id,
                'name' => $model->name,
                'uid' => Auth::id(),
                'username' => $user->name,

            ];
            $message->setAttributes($attributes)->writeLog('add');

        });
        Project::updated(function ($model) {
            $message = app(LogService::class);
            //根据用户id 获取用户名称
            $user = Auth::user();
            $attributes = [
                'type_id' => $model->id,
                'type' => 4,
                'operation_id' => $model->id,
                'name' => $model->name,
                'uid' => Auth::id(),
                'username' => $user->name,

            ];
            $message->setAttributes($attributes)->writeLog('update');

        });
    }
}