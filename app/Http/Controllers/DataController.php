<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Category;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
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

    public function test(Request $request) {
        dd($this->guard()->id());
        dd($request->session()->all());
    }

    protected function create(array $data)
    {
        var_dump('dddd');
        $options = json_encode(array_key_exists('options', $data) ? $data['options'] : []);
        return Category::create([
            'type' => $data['type'],
            'projectName' => $data['projectName'],
            'proAliasName' => $data['proAliasName'],
            'isUsedFor' => $data['isUsedFor'],
            'uid' => $this->guard()->id(),
            'options' => $options
        ]);
    }

    protected function validator(array $data)
    {
        $messages = [
            'type'    => ' :attribute 不匹配.',
            'projectName'    => ':attribute 不匹配',
            'proAliasName' => ':attribute 不匹配',
            'isUsedFor'      => ':attribute 不匹配',
        ];
        return Validator::make($data, [
            'type' => 'required|integer|max:255',
            'projectName' => 'required|min:2|string|max:255',
            'proAliasName' => 'required|string|min:2|max:255',
//            'aliasName' => 'required|string|max:255|unique:users',
            'isUsedFor' => 'required|string',
        ], $messages);
    }

    public function postCategory(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json([
                'code' => 402,
                'msg' => $errors
            ]);
        }
        $category = $this->create($request->all());

        return $this->json([
            'code' => 0,
            'data' => $category,
        ]);

    }
}
