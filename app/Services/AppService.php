<?php

namespace App\Services;

use App\Models\AuditConfig;
use App\Models\AuditProject;
use Illuminate\Database\QueryException;
use DB;

class AppService extends BaseService
{
    private $env = [
        'test',
        'beta',
        'prod'
    ];

    /**
     * @param $data
     * @return bool
     */
    public function createProject($data)
    {
        DB::beginTransaction();
        try {

            $data['env'] = 'prod';
            AuditProject::create($data);

            DB::commit();
        } catch (QueryException $exception) {
            info($exception->getMessage());
            DB::rollback();
            return false;
        }
        return true;
    }

    /**
     * @param $project_id
     * @param $data
     * @param $copy
     * @return bool
     */
    public function createAuditConfigs($project_id, $data, $copy)
    {
        DB::beginTransaction();
        try {
            foreach ($this->env as $env) {
                $data['project_id'] = $project_id;
                $data['env'] = $env;

                $re = AuditConfig::create($data);
                if ($re) {
                    if (!empty($copy)) {
                        foreach ($copy as $item) {

                            $data['project_id'] = $item;

                            AuditConfig::create($data);

                        }
                    }

                } else {
                    DB::rollback();
                    return false;
                }
            }
            DB::commit();
        } catch (QueryException $exception) {
            info($exception->getMessage());
            DB::rollback();
            return false;
        }
        return true;
    }

}