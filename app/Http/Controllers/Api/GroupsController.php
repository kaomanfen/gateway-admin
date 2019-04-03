<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Project;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupsController extends Controller
{
    public function __construct(GroupService $groupService)
    {
        parent::__construct();
        $this->service = $groupService;
    }

    /**
     * 获取用户的登陆权限
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //获取权限
        $userInfo = auth()->user();
        $query = Group::select('id', 'name', 'description');
        if ($userInfo->is_admin == 0) {
            $permssions = Permission::where("uid", $this->uid)->get();
            $groupIds = $projectIds = [];
            if (!empty($permssions)) {
                foreach ($permssions as $k => $item) {
                    $groupIds[] = $item->privilege_id;
                }
            }
            $query = $query->whereIn("id", $groupIds);
        }

        $data = $query->orderBy('id', 'desc')->get();
        $data = $data->keyBy('id');
        return $this->body($data);
    }

    /**
     * 获取分组以及下面的项目权限 目前不用
     * @return \Illuminate\Http\JsonResponse
     */
    public function index1()
    {

        $uid = 139;
        $permssions = Permission::where("uid", $uid)->get();
        $groupIds = $projectIds = [];
        if (!empty($permssions)) {
            foreach ($permssions as $k => $item) {
                if ($item->type == 1) {
                    $groupIds[] = $item->privilege_id;
                } else {
                    $projectIds[] = $item->privilege_id;
                }
            }
        }
        $projectGroupsId = [];
        //获取项目所在分组
        if (!empty($projectIds)) {
            $groups = Project::whereIn("id", $projectIds)->pluck('group_id');
            if (!empty($group)) {
                $projectGroupsId[] = $groups->toArray();
            }
        }
        $allGroups = array_merge($groupIds, $projectGroupsId);
        if (!empty($groupIds)) {
            $groups = Group::select('id', 'name', 'description')->whereIn("id", $allGroups)->orderBy('id',
                'desc')->get();

        }
        $data = [];
        foreach ($groups as $value) {
            $projects = [];
            if (!empty($value->projects)) {
                foreach ($value->projects as $project) {
                    if (in_array($project->group_id, $groupIds)) {
                        $projects[] = [
                            'id' => $project->id,
                            'name' => $project->name,
                            'desc' => $project->desc,
                        ];
                    } else {
                        //获取单个项目
                        if (in_array($project->id, $projectIds)) {
                            $projects[] = [
                                'id' => $project->id,
                                'name' => $project->name,
                                'desc' => $project->desc,
                            ];
                        }

                    }

                }
            }
            $data[$value->id] = [
                "id" => $value->id,
                "name" => $value->name,
                "desc" => $value->description,
                "projects" => $projects,
            ];
        }
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
        $model = Group::findOrFail($id);
        return $this->body($model);
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
            'privilege' => 'required|in:1,2',
        ], [
            'name.required' => '名称必须',
        ]);
        $model = Group::findOrFail($id);
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
    public function destory($id)
    {
        $userInfo = auth()->user();

        if ($userInfo->is_admin == 1) {
            $model = Group::findOrFail($id);
            $count = Project::where('group_id', $id)->count();
            if ($count > 0) {
                return $this->response(0, [], '此分组下有项目，禁止删除');
            }
            $model->status = 0;
            $re = $model->save();
            if ($re) {
                Permission::where('privilege_id', $id)->where('project_id', 0)->delete();
                return $this->response(1, [], '删除成功');
            } else {
                return $this->response(0, [], '删除失败');
            }
        } else {
            return $this->response(0, [], '无权限删除');
        }

    }

    /**
     * 分类
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function projects($id)
    {
        //判断用户是否在分组 如果在 则列出所有项目  否则只列出有权限的项目
        $data = [];
        $userInfo = auth()->user();
        if ($userInfo->is_admin == 0) {
            $model = Permission::where('uid', $this->uid)->where("privilege_id", $id)->where("type", 1)->first();
            if (empty($model)) {
                $model = Permission::where('uid', $this->uid)->where("privilege_id", $id)->where("type",
                    2)->pluck('project_id');
                if (!empty($model)) {
                    $projectIds = $model->toArray();
                    $data = Project::select('id', 'name', 'backend_name')->whereIn('id', $projectIds)->get();
                }

            } else {
                $data = Project::select('id', 'name', 'backend_name')->where('group_id', $id)->get();
            }
        } else {
            $data = Project::select('id', 'name', 'backend_name')->where('group_id', $id)->get();
        }

        return $this->body($data);
    }


}
