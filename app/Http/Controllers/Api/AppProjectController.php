<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditConfig;
use App\Models\AuditProject;
use App\Services\AppService;
use Illuminate\Http\Request;
use App\Http\Requests\companyRequest;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Input;

class AppProjectController extends Controller
{
    protected $request;

    public function __construct(Request $request, AppService $appService)
    {

        //请求数据
        $this->request = $request;
        $this->service = $appService;
        //当前项目id

    }

    /**
     * 列表页面
     */
    public function index()
    {
        $item = AuditProject::all();
        $audit = AuditConfig::select(\DB::raw('count(*) as count, project_id'))
            ->where('env', 'prod')
            ->groupBy('project_id')
            ->pluck('count', 'project_id');
        $data = [];
        if (!empty($audit)) {
            foreach ($item as $k => $v) {
                $data[$k] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'title' => $v['title'],
                    'platform' => $v['platform'],
                    'env' => $v['env'],
                    'num' => array_get($audit, $v['id']),
                ];
            }
        }
        return $this->body($data);
    }

    /**
     * 添加项目
     */
    public function store()
    {
        $this->validate($this->request, [
            'name' => 'required',
            'platform' => 'required|in:ios,android',
        ]);
        $re = $this->service->createProject($this->request->all());
        if ($re) {
            return $this->response(1, [], '操作成功');
        } else {
            return $this->response(1, [], '操作失败，服务异常，请稍后重试');
        }
    }

    /**
     * 修改项目
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $model = AuditProject::findOrFail($id);
        $re = $model->update($this->request->all());
        if ($re) {
            return $this->response(1, [], '操作成功');
        } else {
            return $this->response(1, [], '操作失败，服务异常，请稍后重试');
        }
    }

    /**
     * 删除项目
     * @param $project_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destory($project_id)
    {
        $model = AuditProject::find($project_id);
        if (!empty($model)) {
            $model->delete();
        }
        return redirect("/#app/project/" . $project_id);
    }
}
