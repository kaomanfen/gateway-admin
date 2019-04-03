<?php

namespace App\Services;

use App\Models\Api;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class ApiService extends BaseService
{
    protected $categoryModel;
    private $error;

    public function __construct(Api $model, Category $categoryModel)
    {
        $this->model = $model;
        $this->categoryModel = $categoryModel;
    }

    public function setTable($env)
    {
        $this->model->setTable($env);
        return $this;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * @param $apiId
     * @param string $type
     * @return mixed
     */
    public function copy($apiId, $type = '')
    {
        $model = Api::findOrFail($apiId);
        try {

            //新增
            $data = $model->toArray();
            $data['test_api_id'] = 0;
            $data['beta_api_id'] = 0;
            $data['prod_api_id'] = 0;
            if ($type == 'copy') {
                $data['path'] = $data['path'] . '_copy_' . rand(1, 100);
                $data['version'] = 'v1';
            } else {
                //获取最新版本号
                $lastApi = Api::where('path', $model->path)
                    ->where('method', $model->method)
                    ->where('project_id', $model->project_id)->orderBy('version', 'desc')
                    ->first();

                $v = substr($lastApi->version, 1);
                $version = 'v' . ($v + 1);

                $data['version'] = $version;
                $data['parent_id'] = $apiId;
            }
            $re = Api::create($data);
            return $re->id;
        } catch (QueryException $exception) {
            if ($exception->getCode() == 23000) {
                $this->error = '前端路径，请求方法，版本号必须唯一';
            } else {
                $this->error = $exception->getMessage();
            }
        }


    }

    /**
     * 新增，修改接口
     * @param $input
     * @param int $id
     * @return Api|bool|\Illuminate\Database\Eloquent\Model
     */
    public function updateApi($input, $id = 0)
    {
        $base = $input['base'];
        $frontend = $input['frontend'];
        $backend = $input['backend'];
        $response = $input['response'];

        $data = [
            'project_id' => $base['project_id'],
            'category_id' => $base['category_id'],
            'method' => $frontend['method'],
            'name' => $base['name'],
            'description' => $base['description'],
            'is_auth' => $base['is_auth'],
            'is_sign' => $base['is_sign'],
            'network' => $base['network'],
            'path' => $frontend['path'],
            'version' => $frontend['version'],
            'request' => $frontend['request'],
            'server_path' => $backend['server_path'],
            'timeout' => $backend['timeout'],
            'response_type' => $response['response_type'],
            'response' => $response['response'],
            'response_code' => $response['response_code'],
            'response_text' => $response['response_text'],
            'remark' => $response['remark'],

            'creator' => Auth::id(),
            'status' => 1,
            'upstream_url' => array_get($backend, 'upstream_url', ''),
            'is_cache' => array_get($backend, 'is_cache', ''),
            'try_times' => array_get($backend, 'try_times', '')
        ];
        try {
            if (!empty($id)) {
                //编辑
                $model = Api::findOrFail($id);
                $re = $model->fill($data)->save();
                if ($re) {
                    return $id;
                } else {
                    return false;
                }
            } else {
                //新增
                $data['test_api_id'] = 0;
                $data['beta_api_id'] = 0;
                $data['prod_api_id'] = 0;
                $re = Api::create($data);

                return $re->id;
            }
        } catch (QueryException $exception) {
            if ($exception->getCode() == 23000) {
                $this->error = '前端路径，请求方法，版本号必须唯一';
            } else {
                $this->error = $exception->getMessage();
            }
        }

    }

    /**
     * 左侧菜单接口列表
     * @param $projectId
     * @return array
     */
    public function group($projectId)
    {
        $category = $this->categoryModel->where('project_id', $projectId)->get();

        $data = [];
        foreach ($category as $k => $item) {
            $list = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
            ];
            $children = [];
            foreach ($item->api as $key => $api) {
                $apiName = $api->name;
                if (substr($api->version, 1) > 1) {
                    $apiName .= '[' . $api->version . ']';
                }
                $children[] = [
                    'id' => $api->id,
                    'name' => $apiName,
                ];
            }
            $list['children'] = $children;
            $data[] = $list;

        }
        return $data;
    }

    /**
     * 分页接口列表
     * @param $projectId
     * @param $categoryId
     * @param $uid
     * @param $path
     * @param $name
     * @param string $isAuth
     * @param string $isSign
     * @param string $version
     * @return mixed
     */
    public function getPageList($projectId, $categoryId, $uid, $path, $name, $isAuth = '', $isSign = '', $version = '')
    {
        $model = $this->model->select('id',
            'category_id',
            'version', 'name', 'method', 'path', 'server_path', 'is_sign',
            'is_auth', 'status', 'creator', 'test_api_id', 'beta_api_id',
            'prod_api_id', 'is_cache', 'try_times', 'upstream_url')->with([
            'category' => function ($query) {
                $query->select('id', 'name');
            }
        ])->with([
            'follow' => function ($query) use ($uid) {
                $query->select('api_id')->where('uid', $uid);
            }
        ])->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])->where('project_id', $projectId);
        if (!empty($categoryId)) {
            $model = $model->where('category_id', $categoryId);
        }
        if ($isAuth != '' && in_array($isAuth, [1, 0])) {
            $model = $model->where('is_auth', $isAuth);
        }
        if ($isSign != '' && in_array($isSign, [1, 0])) {
            $model = $model->where('is_sign', $isSign);
        }
        if (!empty($version)) {
            $model = $model->where('version', $version);
        }
        if (!empty($name)) {
            $model = $model->where('name', 'like', '%' . $name . '%');
        }
        if (!empty($path)) {
            $model = $model->where('path', 'like', '%' . $path . '%');
        }
        return $model->orderBy('id', 'desc')->paginate($this->pageSize);
    }

    /**
     * 接口预览
     * @param $id
     * @return array
     */
    public function getDetail($id)
    {
        $info = $this->model->findorFail($id);

        $base = [
            'id' => $info->id,
            'category_id' => $info->category_id,
            'parent_id' => $info->parent_id,
            'category' => array_get($info->category, 'name', '默认分类'),
            'project' => $info->project->name,
            'backend_name' => $info->project->backend_name,
            'project_id' => $info->project_id,
            'name' => $info->name,
            'is_sign' => $info->is_sign,
            'is_auth' => $info->is_auth,
            'network' => $info->network,
            'description' => $info->description,
        ];
        $request = $info->request;
        if (!isset($request['body'])) {
            $request['body'] = [];
        }
        $info->request = $request;

        $frontend = [
            'version' => $info->version,
            'method' => $info->method,
            'path' => $info->path,
            'request' => $info->request,
            'backend_name' => $info->project->backend_name,
        ];
        $backend = [
            'server_path' => $info->server_path,
            'timeout' => $info->timeout,
            'upstream_url' => $info->upstream_url,
            'is_cache' => $info->is_cache,
            'try_times' => $info->try_times,
        ];

        $response = [
            'response_type' => $info->response_type,
            'response' => $info->response,
            'response_code' => $info->response_code,
            'response_text' => $info->response_text,
            'remark' => $info->remark,
        ];
        $data = [
            'base' => $base,
            'frontend' => $frontend,
            'backend' => $backend,
            'response' => $response,
        ];
        return $data;
    }

    /**
     * @param $schema
     * @param $type
     * @return array
     */
    public function SchemaConversionSwagger($schema, $type)
    {
        $data = [];
        if ($type == 'parameters') {
            foreach ($schema as $type => $value) {
                if (!empty($value['properties'])) {
                    foreach ($value['properties'] as $field => $item) {
                        $data[] = [
                            "name" => "uid",
                            "in" => $type,
                            "description" => $item['description'],
                            "required" => isset($value['required'][$field]) ? true : false,
                            "type" => "string",
                            "default" => "20",

                        ];
                    }
                }

            }
        } elseif ($type == 'responseBody') {
            $properties = [];
            foreach ($schema as $type => $value) {
                if (!empty($value['properties'])) {
                    foreach ($value['properties'] as $field => $item) {
                        $properties[$type][$field] = [
                            'type' => $item['type'],
                            'description' => $item['description'],
                            'default' => array_get($item, 'description', ''),
                        ];

                    }
                }

            }
        } else {
            echo 1;
        }
        return $data;
    }
}