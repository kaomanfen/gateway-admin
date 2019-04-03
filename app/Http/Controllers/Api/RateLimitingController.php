<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RateLimitService;
use Illuminate\Http\Request;
use App\Models\RateLimiting;
use App\Models\RateLimitingApi;

class RateLimitingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 限速策略列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = RateLimiting::orderBy('id', 'desc')->get();
        return $this->body($data);
    }


    /**
     * 保存分组
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'period' => 'required|max:100',
        ], [
            'name.required' => '名称必须',
            'period.required' => '周期必须',
        ]);
        $re = RateLimiting::create($request->all());

        if ($re) {
            return $this->response(1, ['id' => $re->id], '新增成功');
        } else {
            return $this->response(0, [], '新增失败，服务端错误');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'period' => 'required|max:100',
        ], [
            'name.required' => '名称必须',
            'period.required' => '周期必须',
        ]);
        $model = RateLimiting::findOrFail($id);
        $data = $request->all();
        $re = $model->update($data);
        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //读取limiting
        $model = RateLimiting::findOrFail($id);
        $api = RateLimitingApi::where('rate_id', $id)->first();
        if ($api) {
            return $this->response(0, [], '此策略下有Api，请先删除');
        }
        $re = $model->delete();
        if ($re) {
            return $this->response(1, [], '删除成功');
        } else {
            return $this->response(0, [], '删除失败');
        }
    }

    /**
     * 绑定api
     * @param Request $request
     * @param $rateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindApi(Request $request, $rateId)
    {
        $apis = $request->input('apis_id');
        $projectId = $request->input('project_id');
        if (!empty($apis)) {
            $now = date("Y-m-d H:I:s");
            $data = [];
            foreach ($apis as $v) {
                $data[] = [
                    'rate_id' => $rateId,
                    'project_id' => $projectId,
                    'api_id' => $v,
                    'created_at' => $now,
                ];
            }
            $re = RateLimitingApi::insert($data);
            if ($re) {
                return $this->response(1, [], '删除成功');
            } else {
                return $this->response(0, [], '删除失败');
            }

        }
    }

    /**
     * 获取项目的所有接口
     * @param RateLimitService $service
     * @param Request $request
     * @param $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function projects(RateLimitService $service, Request $request, $projectId)
    {
        $name = $request->get('name', '');
        $data = $service->getPageList($projectId, $name);

        return $this->body($data);
    }

    /**
     * 策略下的所有绑定的api
     * @param $rateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rateApi($rateId)
    {
        $data = RateLimitingApi::where("rate_id", $rateId)->with([
            'api' => function ($query) {
                return $query->select('name', 'prod_api_id');
            }
        ])->with([
            'project' => function ($query) {
                return $query->select('id', 'name');
            }
        ])->orderBy('id', 'desc')->get();
        return $this->body($data);
    }

    /**
     * 批量删除绑定的api
     * @param Request $request
     * @param $rateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function pluckDestroy(Request $request, $rateId)
    {
        $ids = $request->input('ids');

        if (!empty($ids)) {
            $re = RateLimitingApi::whereIn('id', $ids)->delete();
            if ($re) {
                return $this->response(1, [], '删除成功');
            } else {
                return $this->response(0, [], '删除失败');
            }

        }
    }

}
