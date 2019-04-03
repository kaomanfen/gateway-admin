<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * Class HttpService
 * @package App\Common
 * curl 请求处理类
 */
class HttpService
{
    private $client;
    private $response;

    private $startTime;
    private $endTime;

    /**
     * HttpService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->startTime = time();
        $this->client = $client;
    }

    public function getHeader()
    {
        return $this->response->getHeaders();
    }

    public function getStatus()
    {
        return $this->response->getStatusCode();
    }

    public function getContentType()
    {
        return $this->response->getStatusCode();
    }

    public function getTime()
    {
        return $this->endTime - $this->startTime;
    }

    public function getBody()
    {
        $body = $this->response->getBody()->getContents();
//        dd($body);
        return json_decode($body, 1);
    }

    /**
     * POST 请求
     * @param $url string 请求的url
     * @param $body array 请求参数
     * @param $headers array 请求参数
     * @return HttpService 返回值
     */
    public function post($url, $body = [], $headers = [])
    {
        $this->response = $this->client->post($url, ['json' => $body, 'headers' => $headers]);

        $this->endTime = time();
        return $this;
    }


    /**
     * GET 请求
     * @param $url string 请求的url
     * @param $param array 请求参数
     * @param $headers array 请求参数
     * @return HttpService 返回值
     */
    public function get($url, $param = [], $headers = [])
    {
        $this->response = $this->client->get($url, ['query' => $param, 'headers' => $headers, 'http_errors' => false]);
        $this->endTime = time();
        return $this;
    }


    /**
     * PUT 请求
     * @param $url string 请求的url
     * @param $param array 请求参数
     * @param $headers
     * @return HttpService 返回值
     */
    public function put($url, $param, $headers)
    {

        $this->response = $this->client->put($url, ['json' => $param, 'headers' => $headers]);
        $this->endTime = time();
        return $this;
    }

    /**
     * POST 请求
     * @param $url string 请求的url
     * @param $param array 请求参数
     * @param $headers
     * @return HttpService 返回值
     */
    public function delete($url, $param, $headers)
    {

        $this->response = $this->client->delete($url, ['form_params' => $param, 'headers' => $headers]);
        $this->endTime = time();
        return $this;
    }


}