<?php
/**
 * Created by PhpStorm.
 * User: hellokitty
 * Date: 2018/10/31
 * Time: 23:49
 */

namespace App\Exports;

use Complex\Exception;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use App\Exports\DataTrait;

use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\TablePosition;

class BasicWordExport {

    use DataTrait;


    protected $phpWord;
    protected $basicList;

    public function __construct($basicList)
    {
        $this->phpWord = new PhpWord();
        //配置字体
        $this->phpWord->setDefaultFontName('微软雅黑');
//配置全局的字号
        $this->phpWord->setDefaultFontSize(10);
        $this->basicList = $basicList;
        $this->phpWord->getCompatibility()->setOoxmlVersion(15);

    }

    protected function createTable ($section, $flag) {

        $tableStyle = array(
            'unit' => TblWidth::PERCENT,
            'width' => 10000,
            'borderColor' => '006699',
            'borderSize'  => 6,
            'cellMargin'  => 50,
            'alignment' => $flag ? 'start' : 'end',
            'position' => [
                'tblpX' => 1000,
                'horzAnchor' => TablePosition::HANCHOR_PAGE,
                'tblpXSpec' => $flag ? TablePosition::XALIGN_LEFT : TablePosition::XALIGN_RIGHT
            ],
            'layout' => 'fixed',
        );
        $firstRowStyle = array();


        $time = time();
        $tableName = 'myTable'. $time;

        $this->phpWord->addTableStyle($tableName, $tableStyle, $firstRowStyle);
        $table = $section->addTable($tableName);
        return $table;
    }

    protected function createSection () {
        $section = $this->phpWord->addSection([
//            'orientation' => 'landscape',
            'colsNum' => 2,
            'breakType' => 'continuous',
            'lineNumbering' => 'restart'
        ]);
        return $section;
    }

    protected function computeCellVal($cat, $val) {
        $alias = $cat['proAliasName'];
        if (!array_key_exists($alias, $val)) return '';
        $data = $val[$alias];
        return $data['value'];
    }

    protected function setImageSection($section, $image, $val) {
        if (!is_array($val) || !$image || !array_key_exists('proAliasName', $image)) {
            return ;
        }
        $alias = $image['proAliasName'];
        if (!$alias || !array_key_exists($alias, $val)) {
            return ;
        }
        $info = $val[$alias];
        $path = $info['path'];
//        dd(Storage::get('public/'. $path));
        $imageData = null;
        try {
            $imageData = Storage::get('public/' . $path);
        } catch (Exception $e) {}
        if (!$imageData) return ;
        $style = [
            'width'         => 100,
            'height'        => 100,
            'marginTop'     => -1,
            'marginLeft'    => -1,
        ];

        $section->addImage($imageData, $style);
    }

    public function toWord() {

        $cat = $this->getCardListCat();
        $image = null;
        foreach ($cat as $v) {
            if ($v['type'] == 5) {
                $image = $v;
                break;
            }
        }

        $noImageCat = array_filter($cat, function ($item) {
            return $item['type'] != 5;
        });
        $first4Cat = array_slice($noImageCat, 0, 4);
        $otherCat = array_slice($noImageCat, 4);

        $otherCat = array_chunk($otherCat, 2);

        $data = $this->computeData();

        $rowHeight = 500;
        $cellWidth = 1000;

        $cellStyle = [
//            'bgColor' => 'ffffff',
            'width' => $cellWidth,
            'valign' => 'center',
        ];
        $fontStyle = ['bold' => true];

        if ($data && count($data)) {
            $dex = 1;
            $d = array_chunk($data, 2);

            foreach ($d as $data1) {
                $flag = true;
                $section = $this->createSection();

                foreach ($data1 as $val) {

                    $table = $this->createTable($section, $flag );
                    $flag = false;
                    $index = 0;
                    foreach ($first4Cat as $value) {
                        $row = $table->addRow($rowHeight);

                        $cell = $row->addCell($cellWidth, $cellStyle);
                        $cell->addText($value['projectName'], $fontStyle);

                        $cell1 = $row->addCell($cellWidth, $cellStyle);
                        $currentLineData = $this->computeCellVal($value, $val);
                        $cell1->addText($currentLineData);

                        $needImageRow = $row->addCell($cellWidth, [
                            'textDirection' => Cell::TEXT_DIR_TBRL,
                            'gridSpan' => 2,
                            'vMerge' => 'continue'
                        ]);
                        $this->setImageSection($needImageRow, $image, $val);
                    }
                    foreach ($otherCat as $value) {
                        $row = $table->addRow($rowHeight);
                        if ($value && is_array($value)) {
                            foreach ($value as $item) {
                                $cell = $row->addCell($cellWidth, $cellStyle)->addText($item['projectName'], $fontStyle);

                                $cell1 = $row->addCell($cellWidth, $cellStyle);
                                $currentLineData = $this->computeCellVal($item, $val);
                                $cell1->addText($currentLineData);
                            }
                        }

                    }
                    $section->addTextBreak();

                }
                if ($dex % 2 === 0) {
                    $section->addPageBreak();
                }
                $dex++;
            }
        } else {
            return 'no content';
        }


//        $section->addImage();
        $name = 'data'.date('Y-m-d-i-s').'.doc';
        return $this->phpWord->save($name, 'Word2007', true);
    }
}