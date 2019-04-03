<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $uid = 0;
    protected $service;
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->uid = Auth::id();
    }

    protected function response($status, $data, $message = 'OK')
    {
        $json = [
            'status'=>$status,
            'result'=>$data,
            'message' => $message,
        ];
        return response()->json($json);
    }

    protected function body($data)
    {
        return response()->json($data);
    }
}
