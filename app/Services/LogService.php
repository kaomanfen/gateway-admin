<?php

namespace App\Services;

use App\Models\OperationLog;
use App\Models\Project;
use Kmf\Ding\Services\DingService;

class LogService extends BaseService
{
    //1接口2分类3 权限4 项目5分组
    private $typeMap = [
        '1' => '接口新增修改',
        '2' => '分类',
        '3' => '权限',
        '4' => '项目',
        '5' => '分组',
    ];
    private $sendType = [];

    /**
     * $data = [
     *    "name"=>"dingRobot",
     *    "id"=>"",钉钉机器人或者接口id
     *    "url"=>"http://xx.com",
     *    "title"=>"标题",
     *    "content"=>"内容",
     * ]
     * 记录日志 是否发送钉钉 目前紧支持发送钉钉公告和机器人
     * @param $data  DingRobot follows
     * @return LogService
     */
    public function setSend($data)
    {
        $this->sendType[] = $data;
        return $this;
    }

    private $operation = [
        //接口
        1 => [
            'add' => ['name' => '新增', 'template_key' => 1],
            'update' => ['name' => '更新', 'template_key' => 1],
            'delete' => ['name' => '删除', 'template_key' => 1],
            'prod_release' => ['name' => '发布正式', 'template_key' => 2],
            'beta_release' => ['name' => '发布beta', 'template_key' => 2],
            'test_release' => ['name' => '发布测试', 'template_key' => 2],
            'prod_online' => ['name' => '从正式下线', 'template_key' => 2],
            'beta_online' => ['name' => '从beta下线', 'template_key' => 2],
            'test_online' => ['name' => '从测试下线', 'template_key' => 2],
        ],
        //分类
        2 => [
            'add' => ['name' => '新增', 'template_key' => 3],
            'update' => ['name' => '修改', 'template_key' => 3],
        ],
        4 => [
            'add' => ['name' => '新增', 'template_key' => 4],
            'update' => ['name' => '修改', 'template_key' => 4],
        ],
        5 => [
            'add' => ['name' => '新增', 'template_key' => 5],
            'update' => ['name' => '修改', 'template_key' => 5],
        ],

    ];
    private $attribute;

    //必须包含 type_id,operation_id,type,uid,username
    public function setAttributes($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    //Rock 更新了分类 订单 下的接口 开通订单
    private $template = [
//        1=>'{username} 更新了{category}下的接口<a href="/#/group/{project_id}/api/detail_{operation_id}">{name}</a>'
        1 => '{username} {operation}了[{category}]下的接口 [{name}]',
        2 => '{username} 将接口[{name}]{operation}',
        3 => '{username}  {operation}了分类 [{name}]',
        4 => '{username}  {operation}了项目 [{name}]',
        5 => '{username}  {operation}了分组 [{name}]',
    ];

    public function writeLog($operation, $data = [])
    {
        //计算模板的key
        $replace = $this->attribute;
        $replace['operation'] = $this->operation[$this->attribute['type']][$operation]['name'];
        $templateKey = $this->operation[$this->attribute['type']][$operation]['template_key'];
        $content = $this->rules($templateKey, $replace);
        $insertData = [
            'uid' => $this->attribute['uid'],
            'username' => $this->attribute['username'],
            'content' => $content,
            'operation_id' => $this->attribute['operation_id'],
            'type' => $this->attribute['type'],
            'type_id' => $this->attribute['type_id'],
            'data' => $data,
        ];
        $re = OperationLog::create($insertData);

        if ($re) {
            //发送消息
            if (!empty($this->sendType)) {
                $message = app(MessageService::class);
                foreach ($this->sendType as $value) {
                    $method = 'send' . ucfirst($value['name']);
                    if (empty($value['content'])) {
                        $value['content'] = $content;
                    }
                    $message->$method($value['id'], $value['title'], $value['content'], $value['url']);
                }
            }
            return $content;
        }
        return false;
    }

    /**
     * @param $templateKey
     * @param $data
     * @return mixed|string|string[]|null
     */
    private function rules($templateKey, $data)
    {
        $string = $this->template[$templateKey];
        foreach ($data as $k => $v) {
            $rules = "/\{" . $k . "\}/";
            $string = preg_replace($rules, $v, $string);
        }
        return $string;
    }

    /**
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getLog($id, $type)
    {
        if ($type == 'project') {
            $projectIds = [$id];

        } else {
            $projectIds = Project::where("group_id", $id)->pluck('id');

            info($projectIds);

        }
        info($projectIds);
        $query = OperationLog::whereIn('type_id', $projectIds)->orderBy('id', 'desc')->paginate($this->pageSize);

        return $query;
    }
}
