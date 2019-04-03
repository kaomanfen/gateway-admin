<?php

namespace App\Console\Commands;

use App\Facades\User;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kmf\Ding\Services\DingService;

class OpenDingDing extends Command
{
    protected $signature = 'dingding:sys-data';
    protected $description = '同步 钉钉 用户';

    private $users = [];
    private $groups = [];

    public function dataUserId()
    {
        //对接user_id
        $list = DB::table('user')->get();
        foreach($list as $value) {
            DB::table('users')->where('email', $value->email)->update(['id'=>$value->uid]);
        }

    }
    public function handle()
    {
        $ding = new DingService();
        $ding->getDepartments('16130493');
        $users = $ding->getAllUsers();
        // 用户属性实时查询对比
        foreach ($users as $value) {
            $user = User::getUserByOpenid($value['dingid']);
            echo $value['name']."\n";
            $data = [
                'open_id' => $value['dingid'],
                'name' => $value['name'],
                'email' => $value['email'],
                'avatar' => $value['avatar'],
                'source' => 'dingding',
            ];
            $model = DB::table('user')->where('email', $value['email'])->first();
            if (!empty($model)) {
                $data['id'] = $model->uid;
            }
//            dd($data);
            if (empty($user)) {
                User::createUser($data);
            } else {
                User::updateUserByOpenId($value['dingid'], $data);
            }
        }

    }
}