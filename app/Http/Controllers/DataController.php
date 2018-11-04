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
//        dd($this->guard()->id());
//        dd($request->session()->all());
        $s = [1,2];
        $d = [
            explode('.', '1.2.3'),
            explode('.', '1'),
            explode('.', ''),
        ];
        dd($d);
    }

    protected function create(array $data)
    {
        $options = json_encode(array_key_exists('options', $data) ? $data['options'] : []);

        $categoryData = [
            'type' => $data['type'],
            'projectName' => $data['projectName'],
            'proAliasName' => $data['proAliasName'],
            'submit' => $data['submit'],
            'basic' => $data['basic'],
            'filter' => $data['filter'],
            'cardList' => $data['cardList'],
            'uid' => $this->guard()->id(),
            'options' => $options
        ];
        if (array_key_exists('id', $data) && ($id = $data['id'])) {
            return Category::where('id', $id)->update($categoryData);
        }

        return Category::create($categoryData);
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
            'proAliasName' => array_key_exists('id', $data) ? 'required|string|min:2|max:255' : 'required|string|min:2|max:255|unique:category',
            'isUsedFor' => 'required|string',
        ], $messages);
    }

    public function postCategory(Request $request)
    {
        $postData = $request->post();
        if (!array_key_exists('isUsedFor', $postData)) {
            return $this->json([
                'code' => 400,
                'data' => '缺少isUsedFor 字段',
            ]);
        }
        $validator = $this->validator($postData);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json([
                'code' => 402,
                'msg' => $errors
            ]);
        }
        $isUsedFor = $postData['isUsedFor'];
        $postData['submit'] = substr_count($isUsedFor, '1') > 0 ? 1:0;
        $postData['basic'] = substr_count($isUsedFor, '2') > 0 ? 1:0;
        $postData['filter'] = substr_count($isUsedFor, '3') > 0 ? 1:0;
        $postData['cardList'] = substr_count($isUsedFor, '4') > 0 ? 1:0;
        $category = $this->create($postData);

        return $this->json([
            'code' => 0,
            'data' => $category,
        ]);

    }

    public function deleteCategory(Request $request)
    {

        $id = $request->post('id');
        if (!$id) {
            return response()->json([
                'code' => 400,
                'msg' => 'no id'
            ]);
        }
        $category = Category::where('id', $id)->delete();

        return $this->json([
            'code' => 0,
            'data' => $category,
        ]);

    }
    public function queryCategory(Request $request)
    {
        $q = ['submit' => 1, 'filter' => 1, 'basic' => 1];
        $all = $request->all();
        $data = [];
        foreach ($all as $key => $value) {
            if (array_key_exists($key, $q)) {
                $data[$key] = intval($value);
            }
        }

        return $this->json([
            'code' => 0,
            'data' => Category::where($data)->orderBy('sortId', 'desc')->limit(80)->get()
        ]);
    }

    public function moveOneStep(Request $request) {
        $id = $request->post('id');
        if (!$id) {
            return $this->wjson(400, '', '请求无效');
        }
        $type = $request->post('type');
        if (!$type) {
            return $this->wjson(400, '', '请求无效');
        }
        if ($type == 1) {
            $cat = Category::where(['id' => $id])->increment('sortId');
        } else {
            $cat = Category::where(['id' => $id])->decrement('sortId');
        }
        if (!$cat) {
            return $this->wjson(400, '', '请求无效');
        }
        return $this->wjson(0, ['msg' => '移动成功'], '请求无效');
    }

    public function getFilterCat(Request $request) {
        $catModel = new Category();

        $filters = $catModel->getFilter();
//        $cardList = $catModel->getCardList();
//        $basicList = $catModel->getBasicList();

        return $this->wjson(0, [
            'filters' => $filters,
//            'basicList' => $basicList,
//            'cardList' => $cardList
        ]);
    }
}
