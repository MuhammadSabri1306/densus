<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\Style\Alignment;

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
    public function getCellKey($rowIndex, $colIndex)
    {
        $dividend = $colIndex;
        $colKey = $this->getColKey($colIndex);
        return $colKey . $rowIndex;
    }

    public function getColKey($colIndex)
    {
        $dividend = $colIndex;
        $colKey = '';

        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $colKey = chr(65 + $modulo) . $colKey;
            $dividend = floor(($dividend - $modulo) / 26);
        }

        return $colKey;
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

    public function setCellMergeValue($rowStartIndex, $colStartIndex, $rowEndIndex, $colEndIndex, $value)
    {
        $startCell = $this->getCellKey($rowStartIndex, $colStartIndex);
        $endCell = $this->getCellKey($rowEndIndex, $colEndIndex);
        
        $this->setCellValue($startCell, $value);
        $this->spreadsheet
            ->getActiveSheet()
            ->mergeCells($startCell.':'.$endCell);
    }

    public function setCellWidth($colNumber, $width, $unit = 'pt') {
        $colKey = $this->getColKey($colNumber);
        // dd($this->spreadsheet->getActiveSheet()->getColumnDimension($colKey));
        $this->spreadsheet
            ->getActiveSheet()
            ->getColumnDimension($colKey)
            ->setWidth($width, $unit);
    }

    public function setCellAlignment($rowIndex, $colIndex, $value)
    {
        $cellKey = $this->getCellKey($rowIndex, $colIndex);
        $align = [
            'left' => Alignment::HORIZONTAL_LEFT,
            'center' => Alignment::HORIZONTAL_CENTER,
            'right' => Alignment::HORIZONTAL_RIGHT
        ];

        if($align[$value]) {
            $alignValue = $align[$value];
        } else {
            return false;
        }

        $this->spreadsheet
            ->getActiveSheet()
            ->getStyle($cellKey)
            ->getAlignment()
            ->setHorizontal($alignValue);
    }

    public function setColSizeAuto($colIndex, $value = true)
    {
        $colKey = $this->getColKey($colIndex);
        $this->spreadsheet
            ->getActiveSheet()
            ->getColumnDimension($colKey)
            ->setAutoSize($value);
    }

}