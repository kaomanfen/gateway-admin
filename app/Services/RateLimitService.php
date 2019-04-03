<?php

namespace App\Services;

use App\Models\Api;

class RateLimitService extends BaseService
{

    public function __construct(Api $model)
    {
        $this->model = $model;
    }

    /**
     * 分页接口列表
     * @param $projectId
     * @param $name
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPageList($projectId, $name)
    {
        $model = $this->model->select('name', 'method', 'path', 'prod_api_id as id')->with([
            'ratelimitapi' => function ($query) {
                $query->select('api_id');
            }
        ])->where('project_id', $projectId)->where('prod_api_id', '>', 0);

        if (!empty($name)) {
            $model = $model->where('name', 'like', '%' . $name . '%');
        }

        return $model->orderBy('id', 'desc')->paginate($this->pageSize);
    }

}