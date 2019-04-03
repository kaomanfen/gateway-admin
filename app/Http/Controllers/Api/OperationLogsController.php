<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LogService;

class OperationLogsController extends Controller
{

    public function index(LogService $log, $id, $type)
    {
        $result = $log->getLog($id, $type);
        return $this->body($result);
    }


}