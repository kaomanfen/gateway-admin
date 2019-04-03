<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\RunFormRequest;
use App\Services\RunService;
use App\Http\Controllers\Controller;

class RunController extends Controller
{
    public function __construct(RunService $service)
    {
//        $this->middleware('auth:api');
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @param RunFormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(RunFormRequest $request)
    {
        $path = $request->input('path');
        $method = $request->input('method');
        $serverIp = $request->input('server_ip', '');
        $host = $request->input('host', '');
        $header = $request->input('header', []);
        $query = $request->input('query', []);
        $body = $request->input('body', []);
        $env = $request->input('env', 'test');
        $version = $request->input('version', 'v1');


        $data = $this->service->setEnv($env)->setUrl($path)
            ->setMethod($method)
            ->setVersion($version)
            ->debug($query, $body, $header);
        return $this->body($data);
    }

    public function bulkCase(Request $request)
    {
        $env = $request->get('env', 'test');
        $projectId = $request->get('project_id', 0);
        $collectId = $request->get('collect_id', 0);
        $this->service->setEnv($env)->bulk($projectId, $collectId);
    }
}
