<?php

namespace App\Services;

use App\Models\User;
use Kmf\Ding\Services\DingService;

class UserService extends BaseService
{
    /**
     * @param $uid
     * @return mixed
     */
    public function getUserByOpenid($uid)
    {
        return User::where('open_id', $uid)->first();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createUser($data)
    {
        return User::create($data);
    }

    /**
     * @param $open_id
     * @param $data
     * @return mixed
     */
    public function updateUserByOpenId($open_id, $data)
    {
        return User::where('open_id', $open_id)->update($data);
    }

    /**
     * @param $dingId
     */
    public function getUserByDingDing($dingId)
    {
        $ding = new DingService();

        $users = $ding->queryUserInfo($dingId);
        dd($users);
    }

    /**
     * @param $token
     * @return bool
     */
    public function getUserByToken($token)
    {
        $dingService = new DingService();
        $user = $dingService->verify($token);
        if (!empty($user) && $user->errcode == 1) {
            return false;
        }
        //更新用户信息
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'source' => 'dingding',
        ];
        $model = User::where('open_id', $user->dingid)->first();
        if (!empty($model)) {
            $this->updateUserByOpenId($user->dingid, $data);
        } else {
            //写入
            $data['open_id'] = $user->dingid;
            $this->createUser($data);
        }

        return $user;

    }

    /**
     * @param $dingId
     * @param $data
     * @return mixed
     */
    public function updateUserByDingId($dingId, $data)
    {
        $model = User::where('open_id', $dingId)->first();
        if (!empty($model)) {
            //更新
            $model->name = $data['name'];
            $model->avatar = $data['avatar'];

            $re = $model->save();
        } else {
            $re = $this->createUser($data);
            //写入
        }
        return $re;
    }
}