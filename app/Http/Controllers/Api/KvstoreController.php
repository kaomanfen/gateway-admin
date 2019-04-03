<?php

namespace App\Http\Controllers\Api;

use App\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\StoreService;
use Illuminate\Http\Request;

class KvstoreController extends Controller
{
    //Api请求超时时间，单位s
    const CURLOPT_TIMEOUT = 30;
    const CHCHETIME = 5 * 60;
    protected $cls;// 接口分类
    protected $proid;


    public function index(Request $request, $projectId, $env = 'prod', $key = 'routes')
    {
        if ($env == 'test') {
            $apiUrl = config('project.api_test_url', '');
        } else {
            if ($env == 'beta') {
                $apiUrl = config('project.api_beta_url', '');
            } else {
                $apiUrl = config('project.api_url', '');
            }
        }
        $model = Project::findOrFail($projectId);
        $url = $apiUrl . '/api/kvstore/' . $model->backend_name . '/' . $key;
        $response = Http::get($url);
        $info = $response->getBody();

        if ($info['status'] != 404 && !empty($info['result'])) {
            $result = $info['result'];
        } else {
            $result = $info;
        }
        return $this->body($result);
    }

    public function update(Request $request, StoreService $storeService, $projectId)
    {
        $env = $request->input('env', '');
        $type = $request->input('type', '');
        if (empty($type) || empty($env)) {
            return $this->response(0, [], '类型或者环境必须')->response();
        }
        $re = $storeService->update($env, $projectId, $type);
        if ($re === true) {
            return $this->response(1, [], '缓存更新成功');
        } else {
            $error = $storeService->getError();
            return $this->response(0, [], $error);
        }

    }
}
