<?php

namespace App\Http\Controllers;

use App\Imports\BasicInfoImport;
use App\Model\BasicInfo;
use App\Model\BasicInfoAttach;
use App\Model\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Model\File;
use App\Exports\BasicExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BasicWordExport;


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
        $postData = $request->post();
        $data = [];
        $id = null;
        if (is_array($postData)) {
            foreach ($postData as $key=>$val) {
                if (array_key_exists('id', $val)) {
                    $id = $val;
                    continue;
                }
                array_push($data, $val);
            }
        }
//        $data = [];
//        dd($id);
        $basic = null;
        $isUpdate = false;
        if ($id) {
            $basic = BasicInfo::where($id)->first();
            $isUpdate = true;
        }
        DB::beginTransaction();
        if (!$basic) {
            $basic = $this->saveBasicInfo([
                'uid' => $this->uid(),
                'type' => 1
            ]);
        }

        if ($basic) {
            $bid = $basic['id'];
            if (!$bid) {
                return $this->json([
                    'code' => 400,
                    'msg' => '数据新增失败'
                ]);
            }
            $res = $this->saveBasicInfoAttach($data, $bid, $isUpdate);
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
    protected function saveBasicInfoAttach(Array $data, $bid, $isUpdate = false) {

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

        if ($isUpdate) {
            BasicInfoAttach::where(['bid' => $bid])->delete();
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

    public function searchListBasicInfo(Request $request)
    {
//        dd($request->all());
        $data = $request->post();
        if (count($data) < 1) {
            return $this->wjson(0, []);
        }
        $queryData = [];
        foreach ($data as $key => $value) {
            if (!array_key_exists('type', $value) || !array_key_exists('value', $value)) {
                continue;
            }
            $val = $value['value'];
            $property = $key;
            $type = $value['type'];
            if ($type === 1) {
                $queryData =[
                    [
                        'property', '=', $property
                    ],
                    [
                        'value', 'like', '%'.$val.'%'
                    ]
                ];
            }
            else if ($type === 2) {
                $queryData =[
                    [
                        'property', '=', $property
                    ],
                    [
                        'value', '=', $val
                    ]
                ];
            }
        }

//        dd($queryData);

        $basicModel = new BasicInfo();

//        $basicList = $basicModel->cats()->where($queryData)->get();

        $basicList = BasicInfo::whereHas('cats', function ($query) use ($queryData) {
//            dd($queryData);
            $query->where($queryData);
        })->paginate(10)->toArray();

        foreach ($basicList['data'] as $key => &$val) {
            $val['cats'] = BasicInfoAttach::where('bid', $val['id'])->get()->toArray();
            foreach ($val['cats'] as &$value) {
                if ($value['type'] == 5) {
                    $value['url'] = File::where(['uuid' => $value['value']])->first()->url;
                }
            }
        }

//        dd($basicList);
        return $this->json([
            'code' => 0,
            'data' => $basicList
        ]);
    }


    public function queryBasicInfo(Request $request) {
        $basicInfo = BasicInfo::paginate(10)->toArray();
        $type = $request->get('type');
        $cat = [];
        if ($type == 1) {

        }
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

    public function exportExcel(Request $request) {

        $name = 'basic-info.'.date("Y-m-d-H:i:s").'.xlsx';

        $data = $request->all();
//        dd($data);
        $queryData = [];

        if (array_key_exists('property', $data) && array_key_exists('value', $data) && array_key_exists('type', $data)) {
            $val = $data['value'];
            $property = $data['property'];
            $type = $data['type'];
            if ($type == 1) {
                $queryData = [
                    [
                        'property', '=', $property
                    ],
                    [
                        'value', 'like', '%' . $val . '%'
                    ]
                ];
            } else if ($type == 2) {
                $queryData = [
                    [
                        'property', '=', $property
                    ],
                    [
                        'value', '=', $val
                    ]
                ];
            }
        }

        $basicList = [];
        if (count($queryData) > 0) {
            $basicList = BasicInfo::whereHas('cats', function ($query) use ($queryData) {
                $query->where($queryData);
            })->get()->toArray();
        } else {
            $basicList = BasicInfo::all()->toArray();
        }

        return Excel::download(new BasicExport($basicList), $name);
    }

    public function exportWord(Request $request) {
//        dd(session());

        $name = 'basic-info.'.date("Y-m-d-H:i:s").'.doc';

        $data = $request->all();
        $queryData = [];
        if (array_key_exists('property', $data) && array_key_exists('value', $data) && array_key_exists('type', $data)) {
            $val = $data['value'];
            $property = $data['property'];
            $type = $data['type'];
            if ($type == 1) {
                $queryData = [
                    [
                        'property', '=', $property
                    ],
                    [
                        'value', 'like', '%' . $val . '%'
                    ]
                ];
            } else if ($type == 2) {
                $queryData = [
                    [
                        'property', '=', $property
                    ],
                    [
                        'value', '=', $val
                    ]
                ];
            }
        }


        $basicList = [];
        if (count($queryData) > 0) {
            $basicList = BasicInfo::whereHas('cats', function ($query) use ($queryData) {
                $query->where($queryData);
            })->get()->toArray();
        } else {
            $basicList = BasicInfo::all()->toArray();
        }

        $basicWord = new BasicWordExport($basicList);
        return $basicWord->toWord();

    }

    public function uploadAndImport(Request $request) {

//        dd(str_contains('出生日期','出生'));

        $fileInst = $request->file('uploadExcel');

//        $extension = $fileInst->clientExtension();

        $clientFileName = $fileInst->getClientOriginalName();
        $fileName = Str::random(5).'.'.$clientFileName;

        $path = $fileInst->storeAs('uploadExcel', $fileName);

        $path = storage_path('app/'.$path);

        $import = new BasicInfoImport();

        $import->calculateData($path);

    }

}
