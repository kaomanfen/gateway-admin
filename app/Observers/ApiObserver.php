<?php

namespace App\Observers;

use App\Models\Api;
use App\Models\Follow;
use App\Services\LogService;
use App\Services\MessageService;

class ApiObserver
{
    /**
     * 监听用户创建的事件。
     *
     * @param Api $api
     * @return void
     */
    public function created(Api $api)
    {
        $message = app(LogService::class);
        $attributes = [
            'type_id' => $api->project_id,
            'type' => 1,
            'operation_id' => $api->id,
            'name' => $api->name,
            'uid' => $api->creator,
            'username' => $api->user->name,
            'category' => !empty($api->category) ? $api->category->name : '',
            'project_id' => $api->project_id,
        ];
        $content = $message->setAttributes($attributes)->writeLog('add');
        if (!empty($api->project->ding_robot) && $content != false) {
            $url = '/#/group/' . $api->project_id . '/api/detail_' . $api->id;
            $title = '接口新增';
            $messageService = app(MessageService::class);
            $messageService->sendDingRobot($api->project->ding_robot, $title, $content, 'link', $url);
        }
    }

    /**
     * 监听接口修改事件。
     * @param Api $api
     */

    public function updated(Api $api)
    {

        $message = app(LogService::class);

        $old = $api->getOriginal();
        info($old['request']);
        if ($api->prod_api_id > 0) {

            $old['request'] = json_decode($old['request'], 1);
            $old['response'] = json_decode($old['response'], 1);
            $old['response_text'] = json_decode($old['response_text'], 1);
            $old['response_code'] = json_decode($old['response_code'], 1);
            $current = $api->toArray();
            $data = [
                'old' => $old,
                'current' => $current,
            ];

            $attributes = [
                'type_id' => $api->project_id,
                'type' => 1,
                'operation_id' => $api->id,
                'name' => $api->name,
                'uid' => $api->creator,
                'username' => $api->user->name,
                'category' => $api->category->name,
                'project_id' => $api->project_id,
            ];
            $content = $message->setAttributes($attributes)->writeLog('update', $data);

            //紧紧修改 网关path,version 认证 签名 网络，描述  path header query body 后端path，返回类型 response remark
            $change = $api->getDirty();
            $config = [
                'is_sign' => [1 => '签名', 0 => '不签名'],
                'is_auth' => [1 => '认证', 0 => '不认证', 2 => '不认证但解析'],
                'network' => [1 => '外网', 2 => '内网'],
                'response_type' => [1 => 'json', 2 => 'html', 3 => '透传'],
            ];
            $notice = [
                "path" => ["name" => "网关路径", "value" => $api->path],
                "version" => ["name" => "版本号", "value" => $api->version],
                "method" => ["name" => "请求方式", "value" => $api->method],
                "server_path" => ["name" => "后端路径", "value" => $api->server_path],
                "is_sign" => ["name" => "签名", "value" => $config['is_sign'][$api->is_sign]],
                "is_auth" => ["name" => "认证", "value" => $config['is_auth'][$api->is_auth]],
                'network' => ["name" => "网络", "value" => $config['network'][$api->network]],
                'response_type' => ["name" => "响应类型", "value" => $config['response_type'][$api->response_type]],
                'remark' => ["name" => "备注", "value" => '查看详情'],
            ];

            $updateField = [];
            foreach ($notice as $field => $desc) {
                if (array_key_exists($field, $change)) {

                    $updateField[] = $field;

                }
            }
            $notice['request'] = ['name' => '输入参数', "value" => '查看详情'];
            $notice['response'] = ['name' => '输出参数', "value" => '查看详情'];
            //判断输入 输出 json是否被修改
            if ($this->jsonUpdate($old['request'], $current['request'])) {
                $updateField[] = 'request';
            }
            if ($this->jsonUpdate($old['response'], $current['response'])) {
                $updateField[] = 'response';
            }
            $sendContent = $content;
            if (!empty($updateField)) {
                $sendContent .= "\n\n";
                $sendContent .= "更新内容:\n";
                foreach ($updateField as $value) {
                    $sendContent .= " - " . $notice[$value]['name'] . ":" . $notice[$value]['value'] . "\n";
                }


            }

            $messageService = app(MessageService::class);
            $title = '接口变更';
            $url = config('project.base_url', '') . '/#/group/' . $api->project_id . '/api/detail_' . $api->id;
            //发送钉钉机器人
            info($sendContent);
            if (!empty($api->project->ding_robot) && $content != false) {
                $sendContent .= "\n[查看](" . $url . ")";
                $messageService->sendDingRobot($api->project->ding_robot, $title, $sendContent, 'markdown', $url);
            }
            //发送给关注此接口的人
            $follows = Follow::select('uid', 'api_id')->where('api_id', $api->id)->with([
                'user' => function ($query) {
                    $query->select('email', 'id');
                }
            ])->get();

            if (!empty($follows)) {
                $emails = [];
                foreach ($follows as $k => $value) {
                    if (!empty($value['user'])) {
                        $emails[] = $value['user']['email'];
                    }
                }
                //发送钉钉消息
                $content = "[" . $content . "](" . $url . ")";
                $markdown = [
                    "title" => $title,
                    "text" => $content,
                ];
                $messageService->sendByEmail($emails, json_encode($markdown), 'markdown');
            }
        }

    }

    private function jsonUpdate($old, $new)
    {
        ksort($old);
        ksort($new);
        if (json_encode($old) != json_encode($new)) {
            return true;
        }
        return false;
    }
}