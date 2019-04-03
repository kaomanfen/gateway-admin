<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Category;
use App\Models\CollectSet;
use App\Models\Jwt;
use App\Models\Permission;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\StoreService;

class ProjectsController extends Controller
{
    public function index()
    {
        $projects = Project::select('id', 'name', 'desc')->where('status', 1)->orderBy('id', 'desc')->get();
        $projects = $projects->keyBy('id');
        return $this->body($projects);

    }

    /**
     * 保存项目
     * @param ProjectRequest $request
     * @param StoreService $storeService
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProjectRequest $request, StoreService $storeService)
    {
        $environment = $request->get('environment', []);
        $data = $request->all();
        $jwtSecret = $data["jwt_secret_new"];
        unset($data['environment']);
        $data = array_merge($data, $environment);
        unset($data["jwt_secret"]);
        unset($data["jwt_secret_new"]);
        $re = Project::create($data);
        Jwt::create(
            [
                'project_id' => $re->id,
                'project_name' => $data['backend_name'],
                'secret_key' => $jwtSecret,
            ]
        );
        if ($re) {
            //更新测试环境项目缓存
            $env = 'test';
            $type = 'projects';
            $storeService->update($env, $re->id, $type);
            return $this->response(1, ['id' => $re->id], '新增成功');
        } else {
            return $this->response(0, [], '新增失败，服务端错误');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Project::find($id);
        $data = $model->toArray();

        $jwt = Jwt::where('project_id', $id)->first();
        if ($jwt) {
            $data['jwt_secret'] = $jwt->secret_key;
        } else {
            $data['jwt_secret'] = '';
        }


        if (empty($data['test_servers']['servers'])) {
            $data['test_servers']['servers'] = [];
        }


        if (empty($data['beta_servers']['servers'])) {
            $data['beta_servers']['servers'] = [];
        }


        if (empty($data['prod_servers']['servers'])) {
            $data['prod_servers']['servers'] = [];
        }


        return response($data);
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
        $model = Project::findOrFail($id);
        $data = $request->all();
        $environment = $request->get('environment', []);
        unset($data['environment']);
        $data = array_merge($data, $environment);
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
            $model = Project::findOrFail($id);
            $model->status = 0;
            //删除所有的项目权限
            $re = $model->save();
            if ($re) {
                Permission::where('privilege_id', $model->group_id)->where('project_id', $id)->delete();
                return response(['status' => 1, 'message' => '删除成功']);
            } else {
                return response(['status' => 0, 'message' => '删除失败']);
            }
        } else {
            return response(['status' => 0, 'message' => 'c']);
        }

    }

    /**
     * 分类
     * @param $id
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function category($id, $type)
    {

        if ($type == 'collect') {
            $data[] = ['id' => 0, 'name' => "公共测试集"];
            $collects = CollectSet::select('id', 'name')->where('project_id', $id)->get();
            if (!empty($collects)) {
                $data = array_merge($data, $collects->toArray());
            }

        } else {
            $data = Category::select('id', 'name')->where('project_id', $id)->get();
        }
        return $this->body($data);
    }
}
