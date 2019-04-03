<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Permission;
use App\Models\Project;
use App\Facades\User as Auth;

class AuthController extends Controller
{
    use RegistersUsers;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'index']]);
    }

    public function index(Request $request)
    {
        //获取用户信息
        $token = $request->get('token', '');
        $user = Auth::getUserByToken($token);
        if ($user == false) {
            return response()->json(['error' => '用户不存在'], 401);
        }
        $model = User::where('email', $user->email)->first();
        $token = JWTAuth::fromUser($model);
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return redirect('/#/auth?token=bearer ' . $token);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        $token = auth()->attempt($credentials);
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $roles = [
            'zone' => [],
            'project' => []
        ];
        $userInfo = auth()->user();

        $permissionList = Permission::select('id', 'uid', 'project_id', 'privilege_id', 'role', 'type')
            ->where('uid', '=', $userInfo->id)
            ->get();

        foreach ($permissionList->toArray() as $item) {
            switch ($item['type']) {
                case 1:
                    $roles['zone'][$item['privilege_id']] = $item['role'];
                    break;
                case 2:
                    $roles['project'][$item['project_id']] = $item['role'];
                    break;
            }
        }

        // 如果所属项目没有分组权限 则默认分配 guest
        $groupList = Project::whereIn('id', array_keys($roles['project']))
            ->groupBy('group_id')
            ->get(['group_id']);

        foreach ($groupList->toArray() as $item) {
            if (!in_array($item['group_id'], array_keys($roles['zone']))) {
                $roles['zone'][$item['group_id']] = 'guest';
            }
        }

        // 如果所属分组下存在项目没有权限 则复制分组权限给它
        $projectList = Project::whereIn('group_id', array_keys($roles['zone']))->get(['id', 'group_id']);
        foreach ($projectList->toArray() as $item) {
            if (!in_array($item['id'], array_keys($roles['project']))) {
                $roles['project'][$item['id']] = $roles['zone'][$item['group_id']];
            }
        }
        $roles['is_admin'] = $userInfo->is_admin;
        $userInfo['roles'] = $roles;
        return response()->json($userInfo);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    protected function registered(Request $request, $user)
    {
        info($request->all());
        info($user);
        $token = auth()->tokenById($user->id);
        return $this->respondWithToken($token);
    }
}
