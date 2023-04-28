<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{
    public $spreadsheet;
    private $field;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function setField($field)
    {
        $this->field = [];
        foreach($field as $key => $title) {
            array_push($this->field, [ 'key' => $key, 'title' => $title ]);
        }
    }

    // $rowIndex >= 1, $colIndex >=1
    private function getCellKey($rowIndex, $colIndex)
    {
        $dividend = $colIndex;
        $colKey = '';

        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $colKey = chr(65 + $modulo) . $colKey;
            $dividend = floor(($dividend - $modulo) / 26);
        }

        $rowIndex = (string) $rowIndex;
        return $colKey . $rowIndex;
    }

    public function fill($data, $startRow = 1, $startColumn = 1)
    {
        if(!is_array($this->field) || count($this->field) < 1) {
            return null;
        }
        
        for($x=$startColumn; $x<count($this->field); $x++) {
            $cellKey = $this->getCellKey($startRow, $x);
            $this->setCellValue($cellKey, $this->field[$x]['title']);
            $test[$cellKey] = $this->field[$x]['title'];
        }

        $startRow++;
        for($i=$startRow; $i<count($data); $i++) {
            for($j=$startColumn; $j<count($this->field); $j++) {

                $cellKey = $this->getCellKey($i, $j);
                $dataKey = $this->field[$j]['key'];
                $this->setCellValue($cellKey, $data[$i][$dataKey]);

            }
        }
    }

    public function setCellValue($cellKey, $value)
    {
        $this->spreadsheet
            ->getActiveSheet()
            ->setCellValue($cellKey, $value);
    }
}