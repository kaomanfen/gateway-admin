<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ApiFormRequest;
use App\Models\Api;
use App\Services\ApiService;
use App\Services\CollectService;
use App\Services\ReleaseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
//    private $service;
    public function __construct(ApiService $service)
    {

        $this->service = $service;
        parent::__construct();
    }

    /**
     * @param $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($projectId)
    {

        $data = $this->service->group($projectId);
        return $this->body($data);
    }

    public function category(Request $request, $projectId, $categoryId)
    {

        $isAuth = $request->get('is_auth', '');
        $isSign = $request->get('is_sign', '');
        $name = $request->get('name', '');
        $path = $request->get('path', '');
        $version = $request->get('version', '');
        $data = $this->service->getPageList($projectId, $categoryId, $this->uid, $path, $name, $isAuth, $isSign,
            $version);
        return $this->body($data);
    }

    /**
     * 保存接口数据
     * @param ApiFormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ApiFormRequest $request)
    {
        $re = $this->service->updateApi($request->all());
        if ($re) {
            return $this->response(1, ["id" => $re], '操作成功');
        } else {
            $error = empty($this->service->getError()) ? '操作失败，服务异常，请稍后重试' : $this->service->getError();
            return $this->response(0, [], $error);
        }
    }

    /**
     * 复制或升级接口版本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function copy(Request $request)
    {
        $apiId = $request->get('api_id', 0);
        $type = $request->get('type', '');
        if (empty($apiId)) {
            return $this->response(0, [], '接口不存在');
        }
        if (!in_array($type, ['copy', 'version'])) {
            return $this->response(0, [], '类型不合法');
        }
        $re = $this->service->copy($apiId, $type);
        if ($re) {
            return $this->response(1, ["id" => $re], '操作成功');
        } else {
            $error = empty($this->service->getError()) ? '操作失败，服务异常，请稍后重试' : $this->service->getError();
            return $this->response(0, [], $error);
        }

    }

    /**
     * 接口详情
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->service->getDetail($id);
        return $this->body($data);
    }

    /**
     * 更新接口
     * @param ApiFormRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ApiFormRequest $request, $id)
    {

        $re = $this->service->updateApi($request->all(), $id);
        if ($re) {
            return $this->response(1, ["id" => $re], '操作成功');
        } else {
            $error = empty($this->service->getError()) ? '操作失败，服务异常，请稍后重试' : $this->service->getError();
            return $this->response(0, [], $error);
        }
    }

    /**
     * 删除接口
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //只有当接口全部下线之后 才允许删除，删除操作不可逆
        $model = Api::findOrFail($id);
        if ($model->test_api_id == 0 && $model->beta_api_id == 0 && $model->prod_api_id == 0) {
            if ($model->delete()) {
                //将曾经发布过的接口也删除
                return $this->response(1, [], '操作成功');
            }
            return $this->response(0, [], '删除失败，请稍后再试');
        } else {
            return $this->response(0, [], '该API还未完全下线，请将所有环境中的API全部下线后再进行删除操作');
        }
    }

    /**
     * 接口发布
     * @param ReleaseService $publicService
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function release(ReleaseService $publicService, Request $request, $id)
    {
        //更改apis状态为发布
        $this->validate($request, [
            'env' => 'required',
            'description' => 'required',
        ], [
            'env.required' => '环境必须',
            'description.required' => '描述必须',
        ]);
        $env = $request->get('env', '');
        $description = $request->get('description', '');
        $releaseId = $publicService->setTable($env)->release($id, $description);
        if ($releaseId) {
            return $this->response(1, ["release_id" => $releaseId], '发布成功');
        } else {
            return $this->response(0, [], '发布失败');
        }
    }

    /**
     * 下线
     * @param ReleaseService $releaseService
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function offline(ReleaseService $releaseService, Request $request, $id)
    {
        $this->validate($request, [
            'env' => 'required',
        ], [
            'env.required' => '环境必须',
        ]);
        $env = $request->get('env', '');
        $description = $request->get('description', '');
        $releaseId = $releaseService->setTable($env)->offline($id, $description);
        if ($releaseId) {
            return $this->response(1, [], '操作成功');
        } else {
            return $this->response(0, [], '操作失败');
        }
    }

    /**
     * 运行
     * @param CollectService $service
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function run(CollectService $service, $id)
    {
        $data = $service->run($id);
        return $this->body($data);
    }
}
