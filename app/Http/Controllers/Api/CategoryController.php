<?php

namespace App\Http\Controllers\Api;

use App\Models\Api;
use App\Models\Category;
use App\Services\ApiService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model = Category::select('id', 'platform', 'app_key', 'app_secret', 'status')->get();
        return $this->body($model);
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
        $re = Category::create($request->all());
        if ($re) {
            return $this->response(1, [], '新增成功');
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
            'name' => 'required|max:255',
        ], [
            'name.required' => '名称必须',
        ]);
        $model = Category::findOrFail($id);
        $data = $request->all();
        $re = $model->update($data);
        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }

    /**
     * @param ApiService $service
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response @return \Illuminate\Http\Response
     */
    public function destroy(ApiService $service, $id)
    {
        //判断接口下是否有接口  有则禁止删除
        $api = Api::where('category_id', $id)->count();
        if (!empty($api)) {
            return response(['status' => 0, 'message' => '此分类下有接口禁止删除']);
        }
        $model = Category::findOrFail($id);
        $re = $model->delete();

        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }
}
