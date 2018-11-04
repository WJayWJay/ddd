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


    protected function guard()
    {
        return Auth::guard();
    }

    protected function json ($data) {
        return response()->json($data);
    }

    protected function user() {
        return $this->guard()->user();
    }

    protected function wjson($code, $data = '', $msg = null) {

        return $this->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }
}
