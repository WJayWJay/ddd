<?php

namespace App\Http\Controllers;

use App\Model\BasicInfo;
use App\Model\BasicInfoAttach;
use App\Model\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Model\File;

class BasicInfoController extends Controller
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

    protected function saveFileInfo (array $data) {
        return File::create($data);
    }

    protected function uid() {
        return Auth::id();
    }

    public function upload(Request $request) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $url = Storage::url($path);
        $uuid = Str::uuid()->toString();

        $file = $this->saveFileInfo([
            'path' => $path,
            'url' => $url,
            'uuid' => $uuid,
            'uid' => $this->uid() ?: 0
        ]);

        return $this->json([
            'code' => 0,
            'data' => [
                'url' => $url,
                'uuid' => $uuid
            ]
        ]);
    }

    public function saveInfo(Request $request){
        $data = $request->post();
//        dd($data);
        DB::beginTransaction();
        $basic = $this->saveBasicInfo([
            'uid' => $this->uid(),
            'type' => 1
        ]);
        if ($basic) {
            $bid = $basic['id'];
            $res = $this->saveBasicInfoAttach($data, $bid);
            if ($res) {
                return $this->json([
                    'code' => 0,
                    'data' => [
                        'msg' => '数据新增成功',
                        'id' => $bid
                    ]
                ]);
            } else {
                return $this->json([
                    'code' => 400,
                    'msg' => '数据新增失败'
                ]);
            }
        } else {
            $this->json([
                'code' => 400,
                'msg' => '数据新增失败'
            ]);
        }
    }
    protected function queryCategory() {
        $query = ['submit' => 1];
        return Category::where($query)->get()->toArray();
    }
    protected function saveBasicInfoAttach(Array $data, $bid) {
        $cats = $this->queryCategory();
        $cats = array_map(function($item) {
            return $item['proAliasName'];
        }, $cats);
        $dataArr = array_map(function($i) {
            return $i['property'];
        }, $data);
        $err = [];
        foreach ($cats as $value) {
            if (!in_array($value, $dataArr)) {
                array_push($err, $value);
            }
        }
        $dbErr = [];
        foreach ($data as $val) {
            $property = $val['property'];
            $value = $val['value'];
            if (is_array($value)) {
                $value = implode('-', $value);
            }
            $res = $this->createBasicInfoAttach([
                'property' => $property,
                'value' => $value,
                'type' => $val['type'],
                'categoryId' => $val['categoryId'],
                'bid' => $bid,
                'uid' => $this->uid()
            ]);
            if (!$res) {
                array_push($dbErr, [$property => 1]);
            }
        }
        if (count($dbErr) > 0) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }
    protected function createBasicInfoAttach($data) {
        return BasicInfoAttach::create($data);
    }
    protected function saveBasicInfo($data) {
        return BasicInfo::create($data);
    }



    public function queryBasicInfo(Request $request) {
        $basicInfo = BasicInfo::paginate(10)->toArray();
        foreach ($basicInfo['data'] as $key => &$val) {
            $val['cats'] = BasicInfoAttach::where('bid', $val['id'])->get()->toArray();
            foreach ($val['cats'] as &$value) {
                if ($value['type'] == 5) {
                    $value['url'] = File::where(['uuid' => $value['value']])->first()->url;
                }
            }
        }
        return $this->json([
            'code' => 0,
            'data' => $basicInfo
        ]);
    }


    public function deleteInfo(Request $request) {
        $data = $request->post();
        if (array_key_exists('ids', $data)) {
            $ids = $data['ids'];
            if (is_array($ids)) {
                BasicInfo::destroy($ids);
                BasicInfoAttach::where('bid', $ids)->delete();
                return $this->json([
                    'code' => 0,
                    'data' => [
                        'msg' => '删除成功',
                    ]
                ]);
            }
        }

    }
}
