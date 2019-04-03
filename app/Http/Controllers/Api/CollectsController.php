<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CaseRequest;
use App\Models\CollectSet;
use App\Services\CollectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CollectsController extends Controller
{
    public function __construct(CollectService $service)
    {
        $this->service = $service;
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ], [
            'name.required' => '名称必须',
        ]);
        $re = CollectSet::create($request->all());
        if ($re) {
            return $this->response(1, [], '新增成功');
        } else {
            return $this->response(0, [], '新增失败，服务端错误');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param $type
     * @return \Illuminate\Http\Response
     */
    public function show($id, $type)
    {
        $data = $this->service->run($id, $type);
        return $this->body($data);
    }

    /**
     * 运行保存的接口UI的接口
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function again($id)
    {
        $data = $this->service->run($id);
        return $this->body($data);
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
            'name' => 'required|max:255',
        ], [
            'name.required' => '名称必须',
        ]);
        $model = CollectSet::findOrFail($id);
        $data = $request->all();
        $re = $model->update($data);
        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }

    /**
     * @param CollectService $service
     * @param $collectSetId
     * @return \Illuminate\Http\Response
     */
    public function destroy(CollectService $service, $collectSetId)
    {
        //删除此集合下的所有用例
        $re = $service->deleteCollectSet($collectSetId);
        if ($re) {
            return response(['status' => 1, 'message' => '删除成功']);
        } else {
            return response(['status' => 0, 'message' => '删除失败']);
        }
    }


    /**
     * 保存用例
     * @param CaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCase(CaseRequest $request)
    {
        $re = CollectSet::create($request->all());
        if ($re) {
            return $this->response(1, [], '新增成功');
        } else {
            return $this->response(0, [], '新增失败，服务端错误');
        }
    }
}
