<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Auth::routes();

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    Route::post('register', 'AuthController@register')->name('register');
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
    //回调登陆
    Route::get('/', 'AuthController@index');

});

//分组管理
Route::get('groups', "GroupsController@index");
Route::post('groups', "GroupsController@store");
Route::put('groups/{id}', "GroupsController@update");
Route::get('groups/{id}', "GroupsController@show");
Route::get('groups/{id}/project', "GroupsController@projects");

Route::delete('groups/{id}', "GroupsController@destory");//删除项目
//项目管理
Route::get('projects', "ProjectsController@index");
Route::post('projects', "ProjectsController@store");
Route::put('projects/{id}', "ProjectsController@update");
Route::get('projects/{id}', "ProjectsController@show");
Route::get('projects/{id}/{type}', "ProjectsController@category")->where("type", 'collect|category');
Route::get('projects/{id}/api', "ApiController@index");
Route::get('projects/{id}/api/{categoryId}/category', "ApiController@category");

Route::delete('projects/{id}', "ProjectsController@destory");//删除项目
//项目缓存

Route::get('projects/{id}/kvstore/env/{env}/type/{type}', 'KvstoreController@index');
Route::post('projects/{id}/kvstore',  'KvstoreController@update');

//接口管理
Route::get('apis/{id}', "ApiController@show");
Route::post('apis', "ApiController@store");
Route::post('apis/copy', "ApiController@copy");
Route::put('apis/{id}', "ApiController@update");
Route::delete('apis/{id}', "ApiController@destroy");
//接口调试
Route::get('apis/{id}/run', "ApiController@run");

//接口发布
Route::post('apis/{id}/release', "ApiController@release");
//接口下线
Route::post('apis/{id}/offline', "ApiController@offline");
//集合测试
Route::get('projects/{id}/collect-set', "CollectsController@index");
//测试集合
Route::post('collects', "CollectsController@store");
Route::post('bulk-case', "RunController@bulkCase");

Route::put('collects/{id}', "CollectsController@update");
Route::delete('collects/{collectSetId}', "CollectsController@destroy");
//展示接口运行
Route::get('runs/{id}/{type}', "CollectsController@show")->where("type", 'api|collect');
//接口运行
Route::post('runs', "RunController@index");
Route::post('case', "CaseController@store");
Route::put('case/{id}', "CaseController@update");
Route::delete('case/{id}', "CaseController@destroy");
Route::get('case/project/{projectId}/collect/{collectId}', "CaseController@group");
//秘钥管理
Route::get('secrets', "SecretController@index");
Route::post('secrets', "SecretController@store");
Route::get('secrets/{id}', "SecretController@view");
Route::put('secrets/{id}', "SecretController@update");
Route::delete('secrets/{id}', "SecretController@destroy");
//分类管理
Route::get('category', "CategoryController@index");
Route::post('category', "CategoryController@store");
Route::get('category/{id}', "CategoryController@view");
Route::put('category/{id}', "CategoryController@update");
Route::delete('category/{id}', "CategoryController@destroy");


//App配置项
Route::get('app-projects', 'AppProjectController@index')->name('app.project.index');

Route::post('app-projects', 'AppProjectController@store')->name('app.project.store');
Route::put('app-projects/{project_id}', 'AppProjectController@update')->name('app.project.update');
Route::delete('app-projects/{id}', 'AppProjectController@destroy')->name('app.project.destroy');

Route::get('app-audit/{env}/{project_id}', 'AppAuditController@index')->name('app.audit.index')->where('env', 'test|beta|prod');
Route::post('app-audit/{project_id}', 'AppAuditController@store')->name('app.audit.store');
Route::put('app-audit/{id}', 'AppAuditController@update')->name('app.audit.update');
Route::delete('app-audit/{id}', 'AppAuditController@destroy')->name('app.audit.destroy');

//权限管理
//获取分组或者项目用户
Route::get('permissions/{id}/privilege/{type}', 'PermissionsController@index')->name('app.permissions.index');
//给分组添加权限
Route::post('permissions', 'PermissionsController@store')->name('app.permissions.store');
Route::delete('permissions/{id}', 'PermissionsController@destroy')->name('app.permissions.destroy');
Route::put('permissions/{id}', 'PermissionsController@update')->name('app.permissions.update');
//用户管理
Route::get('users', 'UsersController@index')->name('app.users.index');

//关注
Route::post('follows', 'FollowsController@store')->name('app.follows.store');

Route::get('operation-logs/{id}/{type}', 'OperationLogsController@index')->name('app.operation.index');


//waf
Route::get('wafs', 'WafController@index')->name('app.wafs.index');

//关注
Route::put('wafs/{id}', 'WafController@update')->name('app.wafs.update');

//限速

Route::get('rate-limiting','RateLimitingController@index');//列表
Route::post('rate-limiting','RateLimitingController@store');//创建
Route::put('rate-limiting/{id}','RateLimitingController@update');//修改策略
Route::delete('rate-limiting/{id}','RateLimitingController@destroy');//删除策略
Route::post('rate-limiting/bind-api/{rate_id}', 'RateLimitingController@bindApi');//绑定api

Route::get('rate-limiting/{rate_id}','RateLimitingController@rateApi');//绑定的所有api列表
Route::delete('rate-limiting/pluck-api/{rate_id}','RateLimitingController@pluckDestroy');//批量删除绑定的api

Route::get('rate-limiting/projects/{projectId}','RateLimitingController@projects');//获取项目下的所有api列表，判断接口是否已经被策略绑定
