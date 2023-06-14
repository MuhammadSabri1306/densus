<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Excel
{
    public $spreadsheet;

    public $useColSizeAuto = false;

    private $field;

    public $startRowNumber;
    public $startColNumber;
    public $endRowNumber;
    public $endColNumber;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function createDownload($filename)
    {
        $filename .= '.xls';
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        header('Content-Type: text/xls');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        $writer->save('php://output');
    }

    public function setField($field)
    {
        $this->field = [];
        foreach($field as $key => $title) {
            array_push($this->field, [ 'key' => $key, 'title' => $title ]);
        }
        return $this;
    }

    // $rowNumber >= 1, $colNumber >=1
    public function getCellKey($rowNumber, $colNumber)
    {
        $dividend = $colNumber;
        $colKey = $this->getColKey($colNumber);
        return $colKey . $rowNumber;
    }

    public function getColKey($colNumber)
    {
        $dividend = $colNumber;
        $colKey = '';

        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $colKey = chr(65 + $modulo) . $colKey;
            $dividend = floor(($dividend - $modulo) / 26);
        }

        return $colKey;
    }

    public function selectCell($startCellNumbers, $endCellNumbers = [])
    {
        if(count($startCellNumbers) === 2) {
            $this->startRowNumber = $startCellNumbers[0];
            $this->startColNumber = $startCellNumbers[1];
        }
        if(count($endCellNumbers) === 2) {
            $this->endRowNumber = $endCellNumbers[0];
            $this->endColNumber = $endCellNumbers[1];
        } else {
            $this->endRowNumber = null;
            $this->endColNumber = null;
        }
        return $this;
    }

    public function getSelectedCell()
    {
        if(!$this->startRowNumber || !$this->startColNumber) {
            return null;
        }

        $cellKey = $this->getCellKey($this->startRowNumber, $this->startColNumber);
        if(!$this->endRowNumber || !$this->endColNumber) {
            return $cellKey;
        }
        
        $cellKey .= ':' . $this->getCellKey($this->endRowNumber, $this->endColNumber);
        return $cellKey;
    }

    public function setValue($value, $rowNumber = null, $colNumber = null)
    {
        if($rowNumber && $colNumber) {
            $this->selectCell([$rowNumber, $colNumber]);
        } else {
            $rowNumber = $this->startRowNumber;
            $colNumber = $this->startColNumber;
        }
        
        $cellKey = $this->getCellKey($rowNumber, $colNumber);
        $this->spreadsheet
            ->getActiveSheet()
            ->setCellValue($cellKey, $value);
        
        if($this->useColSizeAuto) {
            $this->setWidthAuto($colNumber);
        }

        return $this;
    }

    public function fill($data)
    {
        if(!is_array($this->field) || count($this->field) < 1) {
            return $this;
        }

        $startRowNumber = $this->startRowNumber;
        $startColNumber = $this->startColNumber;
        
        for($x=$startColNumber; $x<count($this->field); $x++) {
            $cellKey = $this->getCellKey($startRowNumber, $x);
            $this->setValue($this->field[$x]['title'], $startRowNumber, $x);
        }
        
        $startRowNumber++;
        // dd_json($data);
        for($i=$startRowNumber; $i<count($data); $i++) {
            // if($i == 11920) {
                // dd(count($data));
                // 54982
                // dd($i);
            //     dd_json($data[$i + 1]);
            // }

            for($j=$startColNumber; $j<count($this->field); $j++) {

                $dataKey = $this->field[$j]['key'];
                $this->setValue($data[$i][$dataKey], $i, $j);

            }
        }

        return $this;
    }

    public function mergeCell()
    {
        $cell = $this->getSelectedCell();
        $this->spreadsheet
            ->getActiveSheet()
            ->mergeCells($cell);
        return $this;
    }

    public function setWidthAuto($value = true)
    {
        $startColNumber = $this->startColNumber;
        $endColNumber = $this->endColNumber ? $this->endColNumber : $startColNumber;

        for ($colNumber=$startColNumber; $colNumber<=$endColNumber; $colNumber++) {
            $colKey = $this->getColKey($colNumber);
            $this->spreadsheet
                ->getActiveSheet()
                ->getColumnDimension($colKey)
                ->setAutoSize($value);
        }
        return $this;
    }

    public function setWidth($width, $unit = 'pt') {
        $startColNumber = $this->startColNumber;
        $this->endColNumber = $this->endColNumber ? $this->endColNumber : $startColNumber;

        for ($colNumber=$startColNumber; $colNumber<=$endColNumber; $colNumber++) {
            $colKey = $this->getColKey($colNumber);
            $this->spreadsheet
                ->getActiveSheet()
                ->getColumnDimension($colKey)
                ->setWidth($width, $unit);
        }

        return $this;
    }

    public function setAlignment($value)
    {
        $align = [
            'left' => Alignment::HORIZONTAL_LEFT,
            'center' => Alignment::HORIZONTAL_CENTER,
            'right' => Alignment::HORIZONTAL_RIGHT
        ];

        if(!$align[$value]) {
            return $this;
        }

        $alignValue = $align[$value];
        $cellKey = $this->getSelectedCell();
        $this->spreadsheet
            ->getActiveSheet()
            ->getStyle($cellKey)
            ->getAlignment()
            ->setHorizontal($alignValue);
        return $this;
    }

    public function setFill($argbColor)
    {
        $cellKey = $this->getSelectedCell();
        $this->spreadsheet
            ->getActiveSheet()
            ->getStyle($cellKey)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB($argbColor);
        return $this;
    }

    public function setColor($argbColor)
    {
        $cellKey = $this->getSelectedCell();
        $this->spreadsheet
            ->getActiveSheet()
            ->getStyle($cellKey)
            ->getFont()
            ->getColor()
            ->setARGB($argbColor);
        return $this;
    }

    public function setBold($value = true)
    {
        $cellKey = $this->getSelectedCell();
        $this->spreadsheet
            ->getActiveSheet()
            ->getStyle($cellKey)
            ->getFont()
            ->setBold($value);
        return $this;
    }

    public function setBorderColor($argbColor)
    {
        $cellKey = $this->getSelectedCell();
        $this->spreadsheet
            ->getActiveSheet()
            ->getStyle($cellKey)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color($argbColor));
        return $this;
    }

}