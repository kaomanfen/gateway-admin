<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class PermissionsController extends Controller
{
    /**
     * 获取当前用户的分组或者项目的用户以及权限
     * @param $id
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id, $type)
    {
        if ($type == 1) {
            $users = Permission::select('id', 'uid', 'role')->where("privilege_id", $id)->where('type', $type)->with([
                'user' => function ($query) {
                    return $query->select('id', 'name', 'email', 'avatar');
                }
            ])->get();
        } else {
            $users = Permission::select('id', 'uid', 'role')->where("project_id", $id)->where('type', 2)->with([
                'user' => function ($query) {
                    return $query->select('id', 'name', 'email', 'avatar');
                }
            ])->get();
        }

        $data = [];

        if (!empty($users)) {
            foreach ($users as $value) {
                if (!empty($value->user)) {
                    $data[] = [
                        'id' => $value->id,
                        'uid' => $value->uid,
                        'role' => $value->role,
                        'name' => $value->user->name,
                        'email' => $value->user->email,
                        'avatar' => $value->user->avatar,
                    ];
                }
            }
        }
        return $this->body($data);
    }

    /**
     * 给分组或者项目创建权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:master,developer,reporter,guest',
            'uid' => 'required|integer',
            'privilege_id' => 'required',
            'type' => 'required',
        ]);
        $type = $request->input('type', 0);
        $data = $request->all();
        $userId = $data['uid'];
        if ($type == 2) {//项目权限
            //读取分组id
            $projectId = $request->input('project_id', 0);
            $project = Project::findOrFail($projectId);
            $groupId = $project->group_id;
            $data['privilege_id'] = $groupId;

            $validator->after(function ($validator) use ($userId, $groupId, $projectId) {
                info($projectId);
                info($groupId);
                info($this->uid);
                $count = Permission::where('uid', $userId)->where('privilege_id', $groupId)->where("project_id",
                    $projectId)->count();

                if ($count > 0) {
                    $validator->errors()->add('uid', '该用户权限已经分配，请勿重复操作！');
                }
            });

        } else {
            $groupId = $data['privilege_id'];
            $validator->after(function ($validator) use ($userId, $groupId) {
                $count = Permission::where('uid', $userId)->where('privilege_id', $groupId)->where("project_id",
                    0)->count();
                if ($count > 0) {
                    $validator->errors()->add('uid', '该用户权限已经分配，请勿重复操作！');
                }
            });

        }
        $validator->validate();
        $re = Permission::create($data);
        if ($re) {

            return $this->response(1, ["id" => $re], '操作成功');
        } else {
            return $this->response(0, [], '操作失败，服务异常，请稍后重试');
        }
    }

    /**
     * 修改用户所在分组获取所在项目的权限
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'role' => 'required|in:master,developer,reporter,guest',
            'uid' => 'required|integer',
            'privilege_id' => 'required',
            'type' => 'required',
        ]);
        $model = Permission::findOrFail($id);
        $model->role = $request->input('role', 'guest');
        $re = $model->save();
        if ($re) {
            return $this->response(1, ["id" => $id], '操作成功');
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
