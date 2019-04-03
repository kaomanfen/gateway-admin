<?php

namespace App\Services;

use App\Facades\Http;
use App\Models\Api;
use App\Models\ApiCase;
use DB;
use GuzzleHttp\Exception\RequestException;
use Whoops\Exception\ErrorException;

class RunService extends BaseService
{
    private $method;
    private $url;
    private $apiUrl;
    private $env;
    private $version = 'v1';
    private $headers = [];

    public function __construct(Api $model)
    {

        $this->headers['K-Product-Line'] = '';
        $this->headers['Content-Type'] = 'application/json';
        $this->headers['Accept'] = 'application/json';
        $this->model = $model;
    }

    public function setEnv($env)
    {
        $this->env = $env;
        if ($env == 'prod' || $env == 'online') {
            $this->apiUrl = config('project.api_url');
        }elseif($env=='test') {
            $this->apiUrl =config('project.api_test_url');
        }elseif($env=='beta'){
            $this->apiUrl =config('project.api_beta_url');
    }
        return $this;
    }

    public function setUrl($path)
    {

        $this->url = $this->apiUrl . $path;
        $items = explode("/", $path);
        if (!empty($items) && !empty($items[1])) {
            $this->headers['K-Product-Line'] = $items[1];
        }

        return $this;

    }

    public function setMethod($method)
    {
        $this->method = strtolower($method);
        return $this;
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param array $query
     * @param array $body
     * @param array $header
     * @return mixed
     */
    public function debug($query = [], $body = [], $header = [])
    {
        if (in_array($this->method, ['put', 'delete', 'post'])) {
            $request = $body;
        } else {
            $request = $query;
        }
        $this->headers['K-Version'] = $this->version;
        $header = array_merge($this->headers, $header);
        $method = $this->method;
        info($this->url);
        info($header);
        try {
            $result = Http::$method($this->url, $request, $header);
            $data['status'] = $result->getStatus();
            $data['header'] = $result->getHeader();
            $data['body'] = $result->getBody();
            $data['time'] = $result->getTime();
        } catch (RequestException $exception) {
            $data['status'] = $exception->getResponse()->getStatusCode();
            $data['message'] = $exception->getMessage();
            $data['body'] = $exception->getResponse()->getBody();
            $data['header'] = $exception->getResponse()->getHeaders();
            $data['time'] = 0;
        }
        return $data;

    }

    /**
     * 批量运行
     * @param $projectId
     * @param $collectId
     */
    public function bulk($projectId, $collectId)
    {
        //获取项目就信息

        $case = ApiCase::where('project_id', $projectId)->where('collect_id', $collectId)->get();
        foreach ($case as $item) {

            $requestPath = [];
            if (!empty($item->request['path'])) {
                foreach ($item->request['path'] as $value) {
                    $requestPath[$value['name']] = $value['example'];
                }
            }
            $query = [];
            if (!empty($item->request['query'])) {
                foreach ($item->request['query'] as $value) {
                    $query[$value['name']] = $value['example'];
                }
            }
            $header = [];
            if (!empty($item->request['header'])) {
                foreach ($item->request['header'] as $value) {
                    $header[$value['name']] = $value['value'];
                }
            }
            $body = [];
            if (!empty($item->request['body'])) {
                $body = $item->request['body'];
            }
            $path = $this->matchPath($item->path, $requestPath);
            $path = '/' . $item->backend_name . $path;
            $result = $this->setUrl($path)->setMethod($item->method)->setVersion($item->version)
                ->debug($query, $body, $header);
            //状态码 错误消息 响应 写入数据库
            $response = [
                'header' => $result['header'],
                'body' => $result['body'],
            ];
            $item->response = $response;
            $item->env = $this->env;
            $item->status = $result['status'];
            $item->error = substr(array_get($result, 'message', ''), 0, 400);
            $re = $item->save();
            info($re);

            sleep(1);
        }
    }



    /**
     * path 替换
     * @param $path
     * @param $arr
     * @return null|string|string[]
     */
    private function matchPath($path, $arr)
    {
        foreach ($arr as $field => $value) {
            $rules = "/\{(" . $field . ")}/";

            $path = preg_replace($rules, $value, $path);
        }
        return $path;
    }

}