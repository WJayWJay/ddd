<?php

namespace App\Http\Controllers\Auth;

//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends LoginAttachController
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

    public function login(Request $request)
    {
        $this->guard()->logout();
        $valid = $this->checkNotValidated($request);
        if ($valid) return $valid;
        return parent::login($request);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //

        return [
            'code' => 0,
            'data' => $user
        ];
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {

    }

    protected function checkNotValidated(Request $request) {
        $validator = validator(
            $request->all(),
            [
                $this->username() => 'required|string',
                'password' => 'required|string'
            ],
            []);
        if ($validator->fails()) {
            $msgs = $validator->errors()->getMessages();

            return [
                'code' => 400,
                'msg' => '请输入正确的'.$this->getNameError().'或密码'
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
