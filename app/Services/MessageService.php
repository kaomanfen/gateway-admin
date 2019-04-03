<?php

namespace App\Services;

use App\Facades\Http;
use App\Models\Follow;
use App\Models\Project;
use App\Models\User;
use Kmf\Ding\Services\DingService;

class MessageService extends BaseService
{
    private $map = [
        'ding' => 'Kmf\Ding\Services\DingService',
    ];
    private $template = [
        1 => '{username} 更新了{category}下的接口{name}</a>'
    ];

    public function sendByUids($uid, $message)
    {
        $emails = User::whereIn('id', ($uid))->pluck('email');

        if (!empty($emails)) {
            $emails = $emails->toArray();
            $msg = new $this->map['ding'];
            $msg->sendMsgByEmail($emails, $message);
        }

    }

    public function sendByEmail($email, $content, $type = 'text')
    {

        $dingService = new $this->map['ding'];
        info('emailss');
        info($email);
        $dingService->sendMsgByEmail($email, $content, $type);
    }

    /**
     * 发送钉钉机器人 支持项目id 机器人url
     * @param $robot
     * @param $title
     * @param $content
     * @param string $type
     * @param string $url
     * @return
     */
    public function sendDingRobot($robot, $title, $content, $type = '', $url = '')
    {
        if (is_int($robot)) {
            //项目id
            $Dingrobot = '';
            $model = Project::find($robot, ['ding_robot']);
            if (!empty($model) && !empty($model->ding_robot)) {
                $Dingrobot = $model->ding_robot;
            }
        } else {
            $Dingrobot = $robot;
        }
        if ($type == 'link') {
            $url = config('project.base_url', '') . $url;
            $data = [
                'msgtype' => 'link',
                'link' => [
                    'text' => $content,
                    'title' => $title,
                    'messageUrl' => $url,
                ],
            ];
        } elseif ($type == 'markdown') {
            $data = [
                'msgtype' => 'markdown',
                'markdown' => [
                    'title' => $title,
                    'text' => $content,
                ],
            ];
        } else {
            $data = [
                'msgtype' => 'text',
                'text' => [
                    'content' => $content,
                ],
            ];
        }
        if (!empty($Dingrobot)) {
            $re = Http::post($Dingrobot, $data);
            return $re;
        }
    }


    /**
     * 给关注接口的人发送钉钉
     * @param $apiId
     * @param $title
     * @param $content
     * @param $url
     */
    public function sendFollows($apiId, $title, $content, $url = '')
    {
        //发送给关注此接口的人
        $url = config('project.base_url', '') . $url;
        $follows = Follow::select('uid', 'api_id')->where('api_id', $apiId)->with([
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
            if (!empty($url)) {
                $content = "[" . $content . "](" . $url . ")";
            }
            $markdown = [
                "title" => $title,
                "text" => $content,
            ];
            $this->sendByEmail($emails, json_encode($markdown), 'markdown');
        }
    }
}
