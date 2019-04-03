<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
//        if ($request->ajax() || $request->wantsJson())
//        {
//            $json = [
//                'message' => '数据验证不通过',
//                'errors' => $exception->errors(),
//            ];
//            return response()->json($json, 422);
//        }
        return parent::render($request, $exception);
    }
    protected function invalidJson($request, ValidationException $exception)
    {
        $json = [
            'message' => '数据验证不通过',
            'errors' => $exception->errors(),
        ];
        return response()->json($json, $exception->status);
    }
//    protected function unauthenticated($request, AuthenticationException $exception)
//    {
//        var_dump(222);
//        if ($request->expectsJson()) {
//            return response()->json(['error' => 'Unauthenticated.'], 401);
//        }
//
//        return redirect()->guest(route('login'));
//    }
}
