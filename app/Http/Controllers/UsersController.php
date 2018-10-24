<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function list($pae) {

    }


    public function test(Request $request) {
        dd($request->session()->all());
    }

    public function info() {
        return $this->json([
            'code' => 0,
            'data' => $this->guard()->user()
        ]);
    }
}
