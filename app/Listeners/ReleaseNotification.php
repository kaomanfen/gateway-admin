<?php

namespace App\Listeners;

use App\Events\Release;
use App\Services\MessageService;
use Kmf\Ding\Services\DingService;

class ReleaseNotification
{
    public $service;
    private $sendType = [
        1=>'分组',//分组
        2=>'项目',//项目
        3=>'接口发布'//项目
    ];

    /**
     * Create the event listener.
     *
     * @param MessageService $service
     */
    public function __construct(MessageService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the event.
     *
     * @param  Release  $event
     * @return void
     */
    public function handle(Release $event)
    {

        $message = '您已经被赋予'.$this->sendType[$event->permission['type']].":".$event->permission['name'].$event->permission['role'].'的权限';
        $this->service->sendByUids([$event->permission['uid']], $message);
    }
}
