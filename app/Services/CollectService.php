<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiCase;
use App\Models\CollectSet;

class CollectService extends BaseService
{
    protected $collectSet;

    public function __construct(Api $model, CollectSet $collectSet)
    {
        $this->model = $model;
        $this->collectSet = $collectSet;
    }

    /**
     * 获取项目的测试集合
     * @param $projectId
     * @return array
     */
    public function group($projectId)
    {
        $sets = $this->collectSet->where('project_id', $projectId)->get();

        //读取为0的集合
        $list = ApiCase::where("project_id", $projectId)->where('collect_id', 0)->get();
        $commonChildren = [];
        if (!empty($list)) {
            foreach ($list as $value) {
                $commonChildren[] = [
                    "id" => $value->id,
                    "name" => $value->name,
                ];
            }

        }
        $data = [
            ["id" => 0, "name" => "公共测试集", "description" => "默认集合", "children" => $commonChildren]
        ];
        foreach ($sets as $k => $item) {
            $list = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
            ];
            $children = [];
            foreach ($item->collects as $key => $api) {
                $children[] = [
                    'id' => $api->id,
                    'name' => $api->name,
                ];
            }
            $list['children'] = $children;
            $data[] = $list;

        }

        return $data;
    }

    /**
     * 根据项目id 集合id 获取测试用例
     * @param $projectId
     * @param $collectId
     * @return mixed
     */
    public function caseApi($projectId, $collectId)
    {
        $list = ApiCase::select('id', 'api_id', 'name', 'method', 'path', 'backend_name', 'status', 'error', 'response',
            'request')->where('project_id', $projectId)
            ->where('collect_id', $collectId)
            ->get();
        return $list;
    }

    /**
     * 删除集合
     * @param $collectId
     * @return mixed
     */
    public function deleteCollectSet($collectId)
    {
        //删除用例
        ApiCase::where('collect_id', $collectId)->delete();
        //删除集合
        return CollectSet::destroy($collectId);

    }

    /**
     * 删除用例
     * @param $id
     * @return mixed
     */
    public function deleteCollect($id)
    {
        //删除用例
        return ApiCase::destroy($id);
    }

    public function schemaToJson($schema)
    {
        $schema = [
            "type" => 'object',
            "properties" => [
                'goods_id' => [
                    'type' => 'integer',
                    'description' => '购买商品id',
                ],
                'result' => [
                    'description' => '对象',
                    'type' => 'object',
                    "properties" => [
                        "length" => [
                            "type" => "object",
                            'description' => '购买商品id',
                            "properties" => [
                                "abc" => ["type" => "number"],
                                "ddd" => ["type" => "string"],
                            ]
                        ],
                        "width" => [
                            "type" => "number",
                            'description' => '购买商品id',
                        ],
                        "height" => [
                            "type" => "number",
                            'description' => '购买商品id',
                        ]
                    ],
                ],
                'data' => [
                    'type' => 'array',
                    'description' => '类型',
                    "items" => [
                        'type' => 'object',
                        "properties" => [
                            "home" => ["type" => "number"],
                            "age" => ["type" => "integer"],
                            "name" => ["type" => "string"]
                        ],
                    ]
                ],
                'list' => [
                    'type' => 'array',
                    'description' => '类型',
                    "items" => [
                        'type' => 'string',
                    ]
                ],
                'type' => [
                    'type' => 'string',
                    'description' => '类型',
                ],
                'aaa' => [
                    'type' => 'boolean',
                    'description' => '类型',
                ]
            ],
        ];

        $data = SchemaService::schemaToJson($schema);
        dd(json_encode($data));
    }

    public function run($id, $type)
    {
        if ($type == 'api') {
            $model = Api::findOrFail($id);
            $request = $model->request;
            if (!empty($request['body'])) {
                $request['body'] = SchemaService::schemaToJson($request['body']);
            }

            //获取项目环境
            $data = [
                'name' => $model->name,
                'method' => $model->method,
                'version' => $model->version,
                'path' => $model->path,
                'is_auth' => $model->is_auth,
                'is_sign' => $model->is_sign,
                'request' => $request,

            ];
        } else {
            $model = ApiCase::findOrFail($id);
            $request = $model->request;
            if (!empty($request['body'])) {
                $request['body'] = SchemaService::schemaToJson($request['body']);
            }

            $data = [
                'api_id' => $model->api_id,
                'is_auth' => $model->api->is_auth,
                'is_sign' => $model->api->is_sign,
                'version' => $model->api->version,
                'name' => $model->name,
                'status' => $model->status,
                'error' => $model->error,
                'project_id' => $model->project_id,
                'collect_id' => $model->collect_id,
                'env' => $model->env,
                'server_ip' => $model->server_ip,
                'method' => $model->method,
                'path' => $model->path,
                'request' => $model->request,
                'response' => $model->response,
            ];
        }
        info($data);
        return $data;
    }


}