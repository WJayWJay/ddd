<?php

namespace App\Exports;

use App\Model\BasicInfo;
use App\Model\BasicInfoAttach;
use App\Model\Category;
use App\Model\File;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class BasicExport implements FromArray, WithHeadings
//    FromCollection
{

    protected $areaData = null;

    protected $basicList = [];
    public function __construct($basicList) {
        $this->basicList = $basicList;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BasicInfo::all();
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.
        $catModel = new Category();
        $cat = $catModel->getSubmit();
        $arr = array_map(function ($item) {
            return $item['projectName'];
        }, $cat);
        array_unshift($arr, 'id');
        return $arr;
    }

    public function array(): array
    {
        // TODO: Implement array() method.
//        return BasicInfo::all()->toArray();

        return $this->computeData();
    }

    protected function computeArea($val) {
        if (!$val) return $val;
        $areaData = $this->areaData;
        if (!$areaData) {
            $path = resource_path('util/data.json');
            $area = file_get_contents($path);
            $areaData = json_decode($area, true);
            $this->areaData = $areaData;
        }
        $china = $areaData['86'];
        $valArr = explode('-', $val);
        if (!$valArr || !is_array($valArr) || count($valArr) < 1) {
            return $val;
        }
        $len = count($valArr);
        if ($len === 2) {
            return $china[$valArr[0]] . $areaData[$valArr[0]][$valArr[1]];;
        }
        if ($len === 3) {
            return $china[$valArr[0]] . $areaData[$valArr[0]][$valArr[1]] . $areaData[$valArr[1]][$valArr[2]];;
        }
    }

    public function computeData() {
        $catModel = new Category();
        $cat = $catModel->getSubmit();
//        dd($this->basicList);

//        $basicInfo = BasicInfo::all()->toArray();
        $basicInfo = $this->basicList;
        $resArr = [];
        $sum = 1;
        foreach ($basicInfo as $key => &$val) {
            $val['cats'] = BasicInfoAttach::where('bid', $val['id'])->get()->toArray();
            $temp = [$sum++];
            foreach ($val['cats'] as &$value) {

                if ($value['type'] == 5) {
                    $value['url'] = File::where(['uuid' => $value['value']])->first()->url;
                    array_push($temp, env('APP_URL').$value['url']);
                    continue;
                } else if ($value['type'] == 4) {
                    $value['value'] = $this->computeArea($value['value']);
                } else if ($value['type'] == 3) {
                    $value['value'] = Carbon::createFromTimestampMs($value['value'])->format('Y-m-d');
                }
                array_push($temp, $value['value']);
            }
            array_push($resArr, $temp);

        }
        return $resArr;
    }

}
