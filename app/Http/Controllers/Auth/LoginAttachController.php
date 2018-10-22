<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Exception;
use Illuminate\Http\Request;

class LoginAttachController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
//    protected function authenticated(Request $request, $user)
//    {
//        //
//
//        return $user;
//    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        try {
            parent::validateLogin($request);
        } catch (Exception $exception) {

        }
    }

    protected function checkNotValidated(Request $request) {
        $validator = validator($request->all(),
            [
                $this->username() => 'required|string',
                'password' => 'required|string'
            ]
            , []);
        if ($validator->fails()) {
            return [
                'code' => 400,
                'msg' => $validator->errors()->getMessages()
            ];
        }
        return false;
    }

    protected function sendFailedLoginResponse(Request $request) {
        $validator = validator($request->all(),
            [
                $this->username() => 'required|string',
                'password' => 'required|string'
            ]
            , []);
        if ($validator->fails()) {
            return [
                'code' => 400,
                'msg' => $validator->errors()->getMessages()
            ];
        } else {
            return [
                'code' => 400,
                'msg' => $this->getNameError().'或密码错误'
            ];
        }
    }

    protected function getNameError () {
        return [
            'name' => '用户名',
            'email' => '邮箱'
        ][$this->username()];
    }
}
