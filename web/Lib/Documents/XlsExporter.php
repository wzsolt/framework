<?php

use Framework\Models\Session\Ancestor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsExporter {
	private PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet;

	private array $groups = [];

	private array $headers = [];

	public function __construct()
    {
		parent::__construct();

        $this->spreadsheet = new Spreadsheet();
    }

    public function setHeaders(array $headers):self
    {
        $this->headers = $headers;

        return $this;
    }

    public function setGroups(array $groups):self
    {
        $this->groups = $groups;

        return $this;
    }

    public function setTitle(string $title):self
    {
        $worksheet = $this->getActiveSheet();

        $worksheet->setTitle($title);

        return $this;
    }

    public function writeRow(array $data, $row = 1):void
    {
        $i = 1;

        foreach ($data as $value) {
            $this->writeCell($i, $row, $value);

            if(is_array($value) && isset($value['merge']) && $value['merge'] > 1){
                $this->mergeCells($i, $row, $i + $value['merge'] - 1, $row);
                $i += $value['merge'];
            }else {
                $i++;
            }
        }
    }

    public function writeData(array $data, $row = 1):void
    {
        if($data) {
            foreach ($data as $item) {
                $i = 1;

                if(isAssociativeArray($this->headers)){
                    $iterator = $this->headers;
                }else{
                    $iterator = $item;
                }

                foreach ($iterator as $key => $value) {
                    $this->writeCell($i, $row, $item[$key]);

                    if(is_array($item[$key]) && isset($item[$key]['merge']) && $value['merge'] > 1){
                        $this->mergeCells($i, $row, $i + $item[$key]['merge'] - 1, $row);
                        $i += $item[$key]['merge'];
                    }else {
                        $i++;
                    }
                }

                $row++;
            }
        }
    }

    public function writeCell(int $col, int $row, $data):void
    {
        $cellValue = '';

        $worksheet = $this->getActiveSheet();

        if(is_array($data) && isset($data['value'])){
            $cellValue = $data['value'];
        }elseif(!is_array($data)){
            $cellValue = $data;
        }

        $worksheet->setCellValue($this->getNameFromIndex($col) . $row, $cellValue);

        if(is_array($data)){
            if($data['color']) {
                $this->setTextColor($col, $row, $data['color']);
            }

            if($data['background']) {
                $this->setBackgroundColor($col, $row, $data['background']);
            }

            if($data['align']) {
                $this->setTextAlign($col, $row, $data['align']);
            }

            if($data['bold']) {
                $this->setTextBold($col, $row, $data['bold']);
            }

            if($data['italic']) {
                $this->setTextItalic($col, $row, $data['italic']);
            }
        }
    }

    public function setTextColor($col, $row, $value):self
    {
        $worksheet = $this->getActiveSheet();

        $worksheet->getStyle($this->getNameFromIndex($col) . $row)
            ->getFont()
            ->getColor()
            ->setARGB(trim($value, '#'));

        return $this;
    }


    public function setBackgroundColor($col, $row, $value):self
    {
        $worksheet = $this->getActiveSheet();

        $worksheet->getStyle($this->getNameFromIndex($col) . $row)
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB(trim($value, '#'));

        return $this;
    }

    public function setTextBold($col, $row, $value):self
    {
        $worksheet = $this->getActiveSheet();

        $worksheet->getStyle($this->getNameFromIndex($col) . $row)
            ->getFont()
            ->setBold($value);

        return $this;
    }

    public function setTextItalic($col, $row, $value):self
    {
        $worksheet = $this->getActiveSheet();

        $worksheet->getStyle($this->getNameFromIndex($col) . $row)
            ->getFont()
            ->setItalic($value);

        return $this;
    }

    public function setTextAlign($col, $row, $value = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER):self
    {
        $worksheet = $this->getActiveSheet();

        $worksheet->getStyle($this->getNameFromIndex($col) . $row)
            ->getAlignment()
            ->setHorizontal($value);

        return $this;
    }

    public function mergeCells(int $startCol, int $startRow, int $endCol, int $endRow):self
    {
        $worksheet = $this->getActiveSheet();

        $startCell = $this->getNameFromIndex($startCol) . $startRow;

        $endCell = $this->getNameFromIndex($endCol) . $endRow;

        $worksheet->mergeCells($startCell . ':' . $endCell);

        return $this;
    }

    public function getFile(string $fileName, bool $download = false):void
    {
        $writer = new Xlsx($this->spreadsheet);

        $writer->save($fileName);

        if($download){
            $content = file_get_contents($fileName);
            header("Content-Disposition: attachment; filename=" . basename($fileName));

            unlink($fileName);

            exit($content);
        }
    }

    protected function getNameFromIndex(int $num):string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($num);
    }

    protected function getIndexFromName(string $num):int
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($num);
    }

    protected function getActiveSheet(): Worksheet\Worksheet
    {
        return $this->spreadsheet->getActiveSheet();
    }

    protected function addFirstRowBorder(int $startRow = 1):self
    {
        $worksheet = $this->getActiveSheet();

        if($this->groups){
            $startRow++;
        }

        $totalColNumber = count($this->headers);

        $worksheet->getStyle('A' . $startRow . ':' . $this->getNameFromIndex($totalColNumber) . '1')
            ->getFont()->setBold(true);

        $worksheet->getStyle('A' . ($startRow + 1) . ':' . $this->getNameFromIndex($totalColNumber) . '1')
            ->getBorders()
            ->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return $this;
    }

    protected function autoSizeColumns():self
    {
        $worksheet = $this->getActiveSheet();

        $totalColNumber = count($this->headers);

        for ($i = 1; $i <= $totalColNumber; $i++) {
            $col = $this->getNameFromIndex($i);
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this;
    }

    protected function freezePanels(int $startRow = 2):self
    {
        $worksheet = $this->getActiveSheet();

        if($this->groups){
            $startRow++;
        }

        $worksheet->freezePane('A' . $startRow);

        return $this;
    }

    protected function addFilters(int $startRow = 0):self
    {
        $worksheet = $this->getActiveSheet();

        $totalColNumber = count($this->headers);

        if($startRow > 0) {
            $range = 'A' . $startRow . ':' . $this->getNameFromIndex($totalColNumber) . $startRow;
        }else {
            $range = $worksheet->calculateWorksheetDimension();
        }

        $worksheet->setAutoFilter($range);

        return $this;
    }


}