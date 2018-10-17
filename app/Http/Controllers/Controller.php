<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //

    public function json($content) {
        return response()->json($content);
    }
    public function success($content) {
        return $this->json([
            "code" => 0,
            "data" => $content
        ]);
    }
    public function fail($errMsg, $code = 1000, $data = null) {
        if (is_array($errMsg)) {
            $errMsg[1] && $code = $errMsg[1];
            $errMsg[0] && $errMsg = $errMsg[0];
        }
        return $this->json([
            "code" => $code,
            "msg" => $errMsg,
            "data" => $data
        ]);
    }
}
