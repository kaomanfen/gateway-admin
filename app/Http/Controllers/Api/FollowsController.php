<?php

namespace App\Http\Controllers\Api;

use App\Models\Follow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FollowsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model = Follow::where('uid', $this->uid)->get();
        $data = [];
        foreach ($model as $k => $v) {
            $data[] = [
                'uid' => $v->uid,
                'email' => $v->email,
                'api_id' => $v->api_id,
                'api_name' => $v->api->name,
                'api_path' => $v->api->path,
            ];
        }
        return $this->body($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'api_id' => 'required',
        ]);
        $data = $request->all();
        $data['uid'] = $this->uid;
        $re = Follow::create($data);
        if ($re) {
            return $this->response(1, [], '关注成功');
        } else {
            return $this->response(0, [], '关注失败，服务端错误');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //判断接口下是否有接口  有则禁止删除
        $model = Follow::findOrFail($id);

        $re = $model->delete();
        if ($re) {
            return response(['status' => 1, 'message' => '操作成功']);
        } else {
            return response(['status' => 0, 'message' => '操作失败']);
        }
    }
}
