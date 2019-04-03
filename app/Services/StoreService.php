<?php

namespace App\Services;

use App\Facades\Http;
use App\Models\Project;
use DB;
use GuzzleHttp\Exception\RequestException;
use Whoops\Exception\ErrorException;

class StoreService extends BaseService
{
    private $errorIp = [];
    private $error = '';

    /**
     * @param $env
     * @param $type
     * @return bool
     */
    public function updateWafs($env, $type)
    {
        if ($env == 'test') {
            $host = config('project.api_test_url');
        } else {
            if ($env == 'beta') {
                $host = config('project.api_beta_url');
            } else {
                $host = config('project.api_url');
            }
        }

        $apiHosts = config('project.gateway_ip.' . $env, []);
        $re = true;
        if (!empty($apiHosts)) {
            foreach ($apiHosts as $ip) {
                $apiUrl = 'http://' . $ip . '/api/kvstore/global/' . $type;
                info($apiUrl);
                $headers = ['host' => $host];
                try {
                    $response = Http::post($apiUrl, [], $headers);
                    $resultCode = $response->getStatus();
                    info("result===");
                    info($resultCode);
                    if ($resultCode != 200) {
                        $this->errorIp[] = $ip;
                        $re = false;
                    }
                } catch (RequestException $exception) {
                    info($exception->getMessage());
                    $this->errorIp[] = $ip;
                    $re = false;
                }
            }
        } else {
            $this->error = 'IP未找到';
            $re = false;
        }
        return $re;
    }

    /**
     * @param $env
     * @param $projectId
     * @param $type
     * @return bool
     */
    public function update($env, $projectId, $type)
    {
        if ($env == 'test') {
            $host = config('project.api_test_url');
        } else {
            if ($env == 'beta') {
                $host = config('project.api_beta_url');
            } else {
                $host = config('project.api_url');
            }
        }
        $model = Project::findOrFail($projectId);
        $data = [
            'product_line' => $model->backend_name,
            'key' => $type,
        ];

        $apiHosts = config('project.gateway_ip.' . $env, []);
        $re = true;
        if (!empty($apiHosts)) {
            foreach ($apiHosts as $ip) {
                $apiUrl = 'http://' . $ip . '/api/kvstore';
                info($apiUrl);
                $headers = ['host' => $host];
                try {
                    $response = Http::post($apiUrl, $data, $headers);
                    $result = $response->getBody();
                    info("result===");
                    info($result);
                    if ($result['status'] != 200) {
                        $this->errorIp[] = $ip;
                        $re = false;
                    }
                } catch (RequestException $exception) {
                    info($exception->getMessage());
                    $this->errorIp[] = $ip;
                    $re = false;
                }
            }
        } else {
            $this->error = 'IP未找到';
            $re = false;
        }
        return $re;
    }

    public function getError()
    {
        if (empty($this->error) && !empty($this->errorIp)) {
            $this->error = implode(',', $this->errorIp);
            $this->error += '更新出错';
        }
        return $this->error;

    }

}