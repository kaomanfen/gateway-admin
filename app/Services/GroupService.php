<?php

namespace App\Services;

use App\Models\Api;
use App\Models\Group;
use App\Models\Permission;
use DB;
use Illuminate\Database\QueryException;

class GroupService extends BaseService
{
    private $defaultRole = 'owner';
    private $uid;
    private $headers;

    public function __construct(Api $model)
    {
        $this->model = $model;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * 创建分组
     * @param $data
     * @param int $type
     * @return bool
     */
    public function create($data, $type = 1)
    {
        DB::beginTransaction();
        try {
            $re = Group::create($data);
            if ($re) {
                //写入分组owner
                $permiss = [
                    'uid' => $this->uid,
                    'privilege_id' => $re->id,
                    'role' => $this->defaultRole,
                    'type' => $type,
                ];
                if (Permission::create($permiss)) {
                    DB::commit();
                } else {
                    DB::rollback();
                    return false;
                }
            } else {
                DB::rollback();
                return false;
            }

        } catch (QueryException $exception) {
            info($exception->getMessage());
            DB::rollback();
            return false;
        }


        return $re;
    }

}