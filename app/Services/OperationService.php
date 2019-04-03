<?php

namespace App\Services;

use App\Models\Api;
use App\Models\OperationLog;
use DB;
use Whoops\Exception\ErrorException;

class OperationService extends BaseService
{
    private $attribute;

    public function __construct(Api $model)
    {

    }

    /**
     * uid username type  operation_id  必须
     * @param $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @param $operationId
     * @param $content
     * @return mixed
     */
    public function write($operationId, $content)
    {
        $data = [
            'uid' => $this->attribute['uid'],
            'username' => $this->attribute['username'],
            'type' => $this->attribute['type'],
            'operation_id' => $operationId,
            'content' => $content,

        ];
        return OperationLog::create($data);
    }

}