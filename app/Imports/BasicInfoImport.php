<?php

namespace App\Imports;

use App\Model\BasicInfo;
use App\Model\BasicInfoAttach;
use App\Model\Category;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class BasicInfoImport implements ToCollection, WithMappedCells
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        dd($row);

        return new BasicInfo([
            //
        ]);
    }

    public function collection(Collection $collection)
    {
        // TODO: Implement collection() method.

        dd($collection);
    }

    public function mapping(): array
    {
        // TODO: Implement mapping() method.

        return [
            'name'  => 'B',
            'email' => 'C',
        ];
    }

    public function getCat() {
        $cat = new Category();
        return $cat->getSubmit();
    }

    public function findAvartar($id) {
        if (!$id) return '';
        $path = storage_path('app/photos');
        dd($path);
        $files = Storage::files($path);

        dd($files);

        return '';
    }

    public function calculateData($path) {
        $this->findAvartar('aa');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);

        $styleSheet = $spreadsheet->getActiveSheet();

        $data = $styleSheet->toArray();

        $cat = $this->getCat();
        $uid = $this->getId();
        dd($cat);

        $firstRow = $data[0];

        foreach ($data as $key => $val) {
            if ($key === 0) continue;
            $basic = BasicInfo::create([
                'uid' => $uid,
                'type' => 1
            ]);
            $bid = $basic['id'];

            $age = '';

            foreach ($cat as $k => $value) {
                $projectName = $value['projectName'];
                $alias = $value['proAliasName'];
                $type = $value['type'];
                $catId = $value['id'];

                $currentVal = $val[$k+1];
                $index = 0;
                foreach ($firstRow as $rowKey => $rows) {
                    if (str_contains($rows, $projectName)) {
                        $index = $rowKey;
                        break;
                    }
                }
                if ($index) {
                    $currentVal = $val[$index];
                }

                if (str_contains($projectName, '出生')) {
                    $s1 = date_create($currentVal);
                    $s2 = date_create();
                    $diff = date_diff($s1, $s2);
                    $age = $diff->format('%y');
                }
                if (str_contains($projectName, '进入本部门年限')) {
                    $currentVal = $val[$index - 1];
                    $s1 = date_create($currentVal);
                    $s2 = date_create();
                    $diff = date_diff($s1, $s2);
                    $currentVal = $diff->format('%y-%m-%d');
                }

                if (str_contains($projectName, '年龄') && !$currentVal) {
                    $currentVal = $age;
                }
                if (str_contains($projectName, '照片')) {
                    $ai = 0;
                    foreach ($firstRow as $rowKey => $rows) {
                        if (str_contains($rows, '警号')) {
                            $ai = $rowKey;
                            break;
                        }
                    }
                    $cpid = $val[$ai];
                    $currentVal = $this->findAvartar($cpid);
                }


                if ($type === 3) {
//                    dd([$currentVal], 1000 * strtotime($currentVal));

                    $currentVal = 1000 * strtotime($currentVal);
                }
                if (!$currentVal) $currentVal = '';

                $insertData = [
                    'property' => $alias,
                    'value' => $currentVal,
                    'type' => $type,
                    'bid' => $bid,
                    'uid' => $uid,
                    'categoryId' => $catId
                ];

                BasicInfoAttach::create($insertData);
            }
        }
    }

    public function getId () {
        $id = Auth::id();
        return $id;
    }
}
