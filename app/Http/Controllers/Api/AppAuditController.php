<?php

namespace App\Http\Controllers\Api;

use App\Models\Audit;
use App\Models\AuditConfig;
use App\Services\AppService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Input;

class AppAuditController extends Controller
{
    protected $limit = 20;
    protected $proid;
    protected $request;

    public function __construct(Request $request, AppService $appService){
    
        //请求数据
        $this->middleware('auth:api');
        $this->request = $request;
        $this->service = $appService;
        //当前项目id
     
    }

    /**
     * 配置项
     * @param $env
     * @param $project_id
     * @return \Illuminate\Http\JsonResponse
     */
	public function index($env, $project_id)
    {
	    $item = AuditConfig::where('project_id', $project_id)->where('env', $env)->get();
	    return $this->body($item);
	}

    /**
     * 保存配置项
     * @param $project_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($project_id)
    {
        $this->validate($this->request, [
            'name' => 'required|unique:audit_configs,name,env',
            'env' => 'required',
            'value' => 'required',
            'versions' => 'required',
            'remark' => 'required',
        ]);
        $data = $this->request->all();
        //判断name是否存在
        $copy = $this->request->input('copy_project_id', []);
        $re = $this->service->createAuditConfigs($project_id,$data, $copy);
        if ($re) {
            return $this->response(1, [], '操作成功');
        } else {
            return $this->response(1, [], '操作失败，服务异常，请稍后重试');
        }
    }

    /**
     * 删除配置项
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $audit = AuditConfig::find($id);
        if (!empty($audit)) {
            $audit->delete();
        }
        return redirect("/#app/audit/".$id);
    }

    /**
     * 修改项目
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $model = AuditConfig::findOrFail($id);
        $re = $model->update($this->request->all());
        if ($re) {
            return $this->response(1, [], '操作成功');
        } else {
            return $this->response(1, [], '操作失败，服务异常，请稍后重试');
        }
    }
  
}
