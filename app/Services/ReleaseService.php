<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiRelease;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class ReleaseService extends BaseService
{
    private $env;

    public function __construct(ApiRelease $model)
    {
        $this->model = $model;
    }

    public function setTable($env)
    {
        $this->env = $env;
        $this->model->setTable($env);
        return $this;
    }

    /**
     * @param $id
     * @param $description
     * @return bool|mixed
     */
    public function release($id, $description)
    {
        //更新api表的 发布id
        $model = Api::findOrFail($id);
        //插入要发布的表
        $data = [
            'api_id' => $id,
            'project_id' => $model->project_id,
            'method' => $model->method,
            'path' => $model->path,
            'server_path' => $model->server_path,
            'is_sign' => $model->is_sign,
            'is_auth' => $model->is_auth,
            'network' => $model->network,
            'version' => $model->version,
            'request' => $model->request,
            'response_type' => $model->response_type,
            'response' => $model->response,
            'timeout' => $model->timeout,
            'is_cache' => $model->is_cache,
            'try_times' => $model->try_times,
            'upstream_url' => $model->upstream_url,

        ];

        DB::beginTransaction();
        try {
            $field = $this->env . '_api_id';
            if (!empty($model->$field)) {
                //更新
                $envModel = $this->model->where('api_id', $id)->first();
            } else {
                $envModel = $this->model;
            }
            $envModel->setTable($this->env);
            $re = $envModel->fill($data)->save();
            if (!empty($re)) {
                $model->$field = $envModel->id;
                $re = $model->save();
                if ($re) {
                    DB::commit();
                    $storeService = app(StoreService::class);
                    $storeService->update($this->env, $model->project_id, "routes");
                    //写入日志
                    $message = app(LogService::class);
                    //根据用户id 获取用户名称
                    $user = Auth::user();
                    $attributes = [
                        'type_id' => $model->project_id,
                        'type' => 1,
                        'operation_id' => $id,
                        'name' => $model->name,
                        'uid' => Auth::id(),
                        'username' => $user->name,
                    ];
                    $url = '/#/group/' . $model->project_id . '/api/detail_' . $id;
                    $robot = [
                        'name' => 'dingRobot',
                        'title' => '发布接口' . $model->name,
                        'url' => $url,
                        'id' => $model->project_id
                    ];
                    $follows = ['name' => 'follows', 'title' => '发布接口' . $model->name, 'url' => $url, 'id' => $id];
                    $message->setSend($robot)->setSend($follows)->setAttributes($attributes)->writeLog($this->env . '_release');
                    return $envModel->id;
                }
                DB::rollback();
                return false;
            } else {
                DB::rollback();
                return false;
            }
        } catch (QueryException $exception) {
            info($exception->getMessage());
            DB::rollback();
            return false;
        }


    }

    /**
     * 接口下线
     * @param $id
     * @param $description
     * @return bool
     */
    public function offline($id, $description)
    {
        //更新api表的 发布id
        $model = Api::findOrFail($id);
        DB::beginTransaction();
        try {

            $field = $this->env . '_api_id';
            $model->$field = 0;
            if ($model->save()) {
                $re = $envModel = $this->model->where('api_id', $id)->delete();
                if ($re) {
                    DB::commit();
                    $storeService = app(StoreService::class);
                    $res = $storeService->update($this->env, $model->project_id, "routes");
                    //写入日志
                    if ($res === true) {
                        $message = app(LogService::class);
                        //根据用户id 获取用户名称
                        $user = Auth::user();
                        $attributes = [
                            'type_id' => $model->project_id,
                            'type' => 1,
                            'operation_id' => $id,
                            'name' => $model->name,
                            'uid' => Auth::id(),
                            'username' => $user->name,
                        ];

                        $url = '/#/group/' . $model->project_id . '/api/detail_' . $id;
                        $robot = [
                            'name' => 'dingRobot',
                            'title' => '下线接口' . $model->name,
                            'url' => $url,
                            'id' => $model->project_id
                        ];
                        $follows = ['name' => 'follows', 'title' => '下线接口' . $model->name, 'url' => $url, 'id' => $id];
                        $message->setSend($robot)->setSend($follows)->setAttributes($attributes)->writeLog($this->env . '_online');
                    }
                    return true;
                }
            }
            DB::rollback();
            return false;

        } catch (QueryException $exception) {
            info($exception->getMessage());
            DB::rollback();
            return false;
        }


    }

}