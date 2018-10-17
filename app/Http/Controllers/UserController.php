<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class UserController extends BaseController
{

    private $errCode = [
         '10001'=> ['用户名已存在', 10001],
         '10002'=> ['用户注册失败', 10002],
         '10003'=> ['请输入完整信息', 10003],
         '10004'=> ['该邮箱已注册', 10004],
         '10005'=> ['密码位数不能少于6位', 10005],

         '20001'=> ['用户名或密码错误', 20001],
         '20002'=> ['请输入用户名或密码', 20002],
    ];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //

    public function test(Request $request) {
//        print_r($this->getProvider())
        var_dump($request->session()->get('aa'));

    }
    public function register(Request $request) {
        if($request->has('username') && $request->has('password') && $request->has('email')){
            $user = new User();
            $user->username = $request->input('username');

            if (User::where('username', $request->input('username'))->first()) {
                return $this->fail($this->errCode['10001']);
            }
            if (User::where('email', $request->input('email'))->first()) {
                return $this->fail($this->errCode['10004']);
            }
            if (strlen($request->input('password')) < 6) {
                return $this->fail($this->errCode['10005']);
            }

            $user->email = $request->input('email');
            $user->password = $this->getProvider()->getHasher()->make($request->input('password'));
            $user->api_token = str_random(60);
            if($user->save()) {
                return $this->success('用户注册成功');
            } else {
                return $this->fail($this->errCode['10002']);
            }
        } else {
            return $this->fail($this->errCode['10003']);
        }
    }
    public function login(Request $request) {
        $credentials = $this->credentials($request);
        if (!array_key_exists('password', $credentials) || !array_key_exists($this->username(), $credentials)) {
            return $this->fail($this->errCode['20002']);
        }
        $user = $this->getProvider()->retrieveByCredentials($credentials);
        if ($user) {
            $flag = $this->getProvider()->validateCredentials($user, $credentials);
            if ($flag) {
                $api_token = str_random(60);
                $user->api_token = $api_token;
                $user->save();
//                $request->session()->
                return $this->success(['msg' => 'login success']);
            } else {
                return $this->fail($this->errCode['20001']);
            }
        } else {
            return $this->fail($this->errCode['20002']);;
        }
    }

    public function info(Request $request) {
//        $api_token = $request->cookie('api_token');

    }

    protected function getProvider() {
        return $this->guard()->getProvider();
    }
    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->getProvider(
            $this->credentials($request), $request->filled('remember')
        );
    }
    protected function username() {
        return 'username';
    }
    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
