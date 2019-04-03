<?php
/**
 * Created by PhpStorm.
 * User: caoyang
 * Date: 2017/9/5
 * Time: 下午8:14
 */

namespace App\Models;

use App\Services\LogService;
use Illuminate\Support\Facades\Auth;

class Category extends BaseModel
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
    public function api()
    {
        return $this->hasMany('App\Models\Api', 'category_id', 'id');
    }

    /**
     * 查询正常的分类
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePopular($query)
    {
        return $query->where('votes', '>', 100);
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        Category::created(function ($category) {
            $message = app(LogService::class);
            //根据用户id 获取用户名称
            $user = Auth::user();
            info($user->name);
            $attributes = [
                'type_id' => $category->project_id,
                'type' => 2,
                'operation_id' => $category->id,
                'name' => $category->name,
                'uid' => Auth::id(),
                'username' => $user->name,

            ];
            $message->setAttributes($attributes)->writeLog('add');
            info("回调了新增分类");
        });
        Category::updated(function ($category) {
            $message = app(LogService::class);
            //根据用户id 获取用户名称
            $user = Auth::user();
            info($user->name);
            $attributes = [
                'type_id' => $category->project_id,
                'type' => 2,
                'operation_id' => $category->id,
                'name' => $category->name,
                'uid' => Auth::id(),
                'username' => $user->name,
            ];
            $message->setAttributes($attributes)->writeLog('update');
            info("回调了修改了分类");
        });

    }
}