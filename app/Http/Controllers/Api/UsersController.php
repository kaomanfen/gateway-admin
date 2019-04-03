<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * 获取所有用户
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'avatar')->orderBy('id', 'desc')->get();

        return $this->body($users);
    }

    /**
     * 给分组或者项目创建权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'role' => 'required',
            'uid' => 'required|integer',
            'privilege_id' => 'required',
            'type' => 'required',
        ]);
        $re = Permission::create($request->all());
        if ($re) {
            return $this->response(1, ["id" => $re], '操作成功');
        } else {
            return $this->response(0, [], '操作失败，服务异常，请稍后重试');
        }
    }

    /**
     * 修改用户所在分组获取所在项目的权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'role' => 'required',
            'uid' => 'required|integer',
            'privilege_id' => 'required',
            'type' => 'required',
        ]);
        $re = Permission::create($request->all());
        if ($re) {
            return $this->response(1, ["id" => $re], '操作成功');
        } else {
            return $this->response(0, [], '操作失败，服务异常，请稍后重试');
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
        $model = Permission::findOrFail($id);
        $re = $model->delete();
        if ($re) {
            return response(['status' => 1, 'message' => '删除成功']);
        } else {
            return response(['status' => 0, 'message' => '删除失败']);
        }
    }
}
