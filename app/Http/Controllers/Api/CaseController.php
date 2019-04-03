<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CaseRequest;
use App\Models\ApiCase;
use App\Services\CollectService;
use App\Http\Controllers\Controller;

class CaseController extends Controller
{
    public function __construct(CollectService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取这个项目下的测试集合以及例子
     * @param $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($projectId)
    {
        $data = $this->service->group($projectId);
        return $this->body($data);
    }

    /**
     * 获取集合下的测试用例
     * @param $projectId
     * @param $collectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function group($projectId, $collectId)
    {
        $data = $this->service->caseApi($projectId, $collectId);
        return $this->body($data);
    }


    /**
     * 更新测试用例
     *
     * @param CaseRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CaseRequest $request, $id)
    {

        $model = ApiCase::findOrFail($id);
        $data = $request->all();
        if (isset($data['api_id'])) {
            unset($data['api_id']);
        };
        $re = $model->update($data);
        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }

    /**
     * 保存用例
     * @param CaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CaseRequest $request)
    {
        $data = $request->all();
        if (empty($data['status'])) {
            $data['status'] = '';
        }
        $re = ApiCase::create($data);
        if ($re) {
            return $this->response(1, [], '新增成功');
        } else {
            return $this->response(0, [], '新增失败，服务端错误');
        }
    }

    /**
     * 删除用例
     * @param CollectService $service
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CollectService $service, $id)
    {
        //删除此集合下的所有用例
        $re = $service->deleteCollect($id);
        if ($re) {
            return response(['status' => 1, 'message' => '删除成功']);
        } else {
            return response(['status' => 0, 'message' => '删除失败']);
        }
    }
}
