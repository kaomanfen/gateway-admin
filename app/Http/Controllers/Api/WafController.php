<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StoreService;
use Illuminate\Http\Request;
use App\Models\Waf;

class WafController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户的登陆权限
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $query = Waf::select('id', 'name', 'title', 'rules', 'type', 'status', 'created_at', 'updated_at');


        $data = $query->orderBy('id', 'desc')->get();

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
            'name' => 'required|max:255',
            'privilege' => 'required|in:1,2',
        ], [
            'name.required' => '名称必须',
        ]);
        $re = $this->service->setUid($this->uid)->create($request->all(), 1);

        if ($re) {
            return $this->response(1, ['id' => $re->id], '新增成功');
        } else {
            return $this->response(0, [], '新增失败，服务端错误');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $model = Waf::findOrFail($id);
        return $this->body($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param StoreService $storeService
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StoreService $storeService, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:20',
            'title' => 'required|max:100',
            'status' => 'in:0,1',
        ], [
            'name.required' => '英文标识必须',
            'title.required' => '标题必须',
        ]);
        $model = Waf::findOrFail($id);
        $data = $request->all();
        $model->update($data);

        //通过网关更新
        $env = 'prod';
        $res = $storeService->updateWafs($env, 'waf');
        if ($res === true) {
            return $this->response(1, [], '操作成功');
        } else {
            $error = $storeService->getError();
            return $this->response(0, [], $error);
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
        $model = Waf::findOrFail($id);
        $model->status = 0;
        $re = $model->save();
        if ($re) {
            return $this->response(1, [], '删除成功');
        } else {
            return $this->response(0, [], '删除失败');
        }
    }
}
