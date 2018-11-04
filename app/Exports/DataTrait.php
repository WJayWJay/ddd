<?php
/**
 * Created by PhpStorm.
 * User: hellokitty
 * Date: 2018/11/1
 * Time: 00:11
 */

namespace App\Exports;

use App\Model\BasicInfo;
use App\Model\BasicInfoAttach;
use App\Model\Category;
use App\Model\File;
use Illuminate\Support\Carbon;

trait DataTrait
{
    protected $areaData;

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
        if (!str_contains($val, '-')) return $val;
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

    public function getCategory () {

        $catModel = new Category();
        $cat = $catModel->getSubmit();

        return $cat;
    }

    public function getCardListCat() {
        $catModel = new Category();
        $cat = $catModel->getCardList();

        return $cat;
    }

    public function computeData() {

//        $basicInfo = BasicInfo::all()->toArray();
        $basicInfo = $this->basicList;
        $resArr = [];
        $sum = 1;
        foreach ($basicInfo as $key => &$val) {
            $val['cats'] = BasicInfoAttach::where('bid', $val['id'])->get()->toArray();
            $temp = [$sum++];
            foreach ($val['cats'] as &$value) {

                if ($value['type'] == 5) {
                    $file = File::where(['uuid' => $value['value']])->first();
                    $value['url'] = $file->url;
                    $value['path'] = $file->path;
//                    array_push($temp, env('APP_URL').$value['url']);
//                    continue;
                } else if ($value['type'] == 4) {
                    $value['value'] = $this->computeArea($value['value']);
                } else if ($value['type'] == 3) {
                    $value['value'] = Carbon::createFromTimestampMs($value['value'])->format('Y-m-d');
                }
                $temp[$value['property']] = $value;
            }
            array_push($resArr, $temp);
        }
        return $resArr;
    }

}