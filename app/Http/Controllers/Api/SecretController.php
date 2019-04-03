<?php

namespace App\Http\Controllers\Api;

use App\Models\Secret;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SecretController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model = Secret::select('id', 'name', 'app_key', 'app_secret', 'status')->get();
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
            'app_key' => 'required|max:255',
            'app_secret' => 'required|max:255',
        ], [
            'name.required' => '名称必须',
            'app_key.required' => 'appKey必须',
            'app_secret.required' => '秘钥必须',
        ]);
        $re = Secret::create($request->all());
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
        $model = Secret::findOrFail($id);

        $model->name = $request->get('name');
        $re = $model->save();
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
        $model = Secret::findOrFail($id);
        $model->status = 2;
        $re = $model->save();
        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }
}
