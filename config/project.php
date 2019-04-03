<?php

return [

	//防止post频繁快速请求时间间隔，单位s
    'quickTime' => '30',
    //城市Api请求量接口
    'areaNumber' => '/statistics/v1/area',
    //项目更新
    'gateway' => env('GATEWAY_URL', 'https://test-api.gateway.com'),
    'access_log' => env('ACCESS_LOG', '/usr/local/nginx/log/'),
    'error_log' => env('ERROR_LOG', '/usr/local/nginx/log/'),

    'online_access_log' => env('ONLINE_ACCESS_LOG', '/usr/local/nginx/log/'),
    'online_error_log' => env('ONLINE_ERROR_LOG', '/usr/local/nginx/log/'),
    'api_url'=> env('KVSTORE_API', ''),
    'api_beta_url'=> env('BETA_KVSTORE_API', 'https://beta-api.gateway.com/'),
    'api_test_url'=> env('TEST_KVSTORE_API', ''),
    'base_url'=> env('BASE_URL', 'https://admin-api.gateway.com'),
    'top_domain'=> env('BASE_URL', 'gateway.com'),
    'gateway_ip'=> [
        "test"=>explode(",",env('GATEWAY_IP_TEST', '')),
        "beta"=>explode(",",env('GATEWAY_IP_BETA', '')),
        "prod"=>explode(",",env('GATEWAY_IP_PROD', '')),
    ],
];
