<?php

use Framework\Models\Session\Ancestor;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet;

abstract class XlsImporter {
	private PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet;

    private array $worksheets = [];

    private string $selectedWorksheet = '';

    private bool $stopProcessing = false;

	protected int $totalRows = 0;

	abstract public function init($params):void;

    abstract public function validate(string $fileName, int $worksheetId = 0):bool;

    abstract protected function processCell(Cell $cell, int $rowIndex):void;

	abstract protected function processRow(array $data, int $rowIndex):bool;

	abstract protected function getResult():array;

	public function __construct()
    {
		parent::__construct();
	}

	public function doImport(string $fileName, int $worksheetId = 0):array
    {
        $this->stopProcessing = false;
        $result = false;
        $r = 1;

	    $this->loadFile($fileName, $worksheetId);

        $worksheet = $this->spreadsheet->getActiveSheet();
        if($worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $data = [];

                $cellIterator = $row->getCellIterator();

                $cellIterator->setIterateOnlyExistingCells(true);

                foreach ($cellIterator as $cell) {
                    $key = preg_replace("/[^A-Z]+/", "", $cell->getCoordinate());

                    $this->processCell($cell, $r);

                    $data[$key] = $cell->getValue();
                }

                if($this->processRow($data, $r)){
                    $this->totalRows++;
                }

                if($this->stopProcessing) break;

                $r++;
            }

            $result = $this->getResult();
        }

		return $result;
	}

    public function getWorksheets(string $fileName):array
    {
        $this->loadFile($fileName);

        $worksheets = [];

        foreach($this->worksheets AS $id => $name){
            $worksheets[] = [
                'id' => $id,
                'name' => $name['worksheetName']
            ];
        }

        return $worksheets;
    }

    protected function getSelectedWorksheetName():string
    {
        return $this->selectedWorksheet;
    }

    protected function formatTimestamp($timestamp):string
    {
        return date('Y-m-d H:i:s', strtotime(str_replace('.', '-', $timestamp)));
    }

    protected function formatDate($timestamp):string
    {
        if(is_numeric($timestamp)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($timestamp);
        }else{
            $date = str_replace(['.', '/'], '-', $timestamp);
            $date = strtotime($date);
        }
        return date('Y-m-d', $date);
    }

    protected function formatTime($time):string
    {
        if(is_numeric($time)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($time);
        }else{
            $date = str_replace(['.', '/'], '-', $time);
            $date = strtotime($date);
        }
        return date('H:i', $date);
    }

    protected function zeroOnEmpty($data)
    {
        return (Empty($data) ? 0 : $data);
    }

    protected function getNameFromIndex(int $num):string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($num);
    }

    protected function getIndexFromName(string $num):int
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($num);
    }

    protected function checkMergedCell(Cell $cell):bool
    {
        $sheet = $this->getActiveSheet();

        foreach ($sheet->getMergeCells() as $cells) {
            if ($cell->isInRange($cells)) {
                return true;
            }
        }

        return false;
    }

    protected function getActiveSheet(): Worksheet\Worksheet
    {
        return $this->spreadsheet->getActiveSheet();
    }

    protected function getRow($rowIndex, bool $existingCellsOnly = true):PhpOffice\PhpSpreadsheet\Worksheet\CellIterator
    {
        $row = $this->getActiveSheet()->getRowIterator($rowIndex)->current();

        $cellIterator = $row->getCellIterator();

        $cellIterator->setIterateOnlyExistingCells($existingCellsOnly);

        return $cellIterator;
    }

    protected function getCellBackgroundColor(string $cellCoordinate):string
    {
        return $this->getActiveSheet()->getStyle($cellCoordinate)->getFill()->getStartColor()->getRGB(); // ->getARGB();
    }

    protected function getCellColor(string $cellCoordinate):string
    {
        return $this->getActiveSheet()->getStyle($cellCoordinate)->getFont()->getColor()->getRGB(); // ->getARGB();
    }

    protected function getCellComment(string $cellCoordinate):string
    {
        return $this->getActiveSheet()->getComment($cellCoordinate);
    }

    protected function getCellHyperLink(string $cellCoordinate):string
    {
        if($this->getActiveSheet()->getCell($cellCoordinate)->hasHyperlink()){
            return $this->getActiveSheet()->getCell($cellCoordinate)->getHyperlink()->getUrl();
        }

        return '';
    }

    protected function stopProcessing():void
    {
        $this->stopProcessing = true;
    }

    protected function loadFile(string $inputFileName, int $worksheetId = 0):void
    {
        $inputFileType = IOFactory::identify($inputFileName);
        $reader = IOFactory::createReader($inputFileType);

        //$reader->setReadDataOnly(true);

        $this->worksheets = $reader->listWorksheetInfo($inputFileName);

        $this->selectedWorksheet = $this->worksheets[$worksheetId]['worksheetName'];

        $reader->setLoadSheetsOnly($this->selectedWorksheet);

        $this->spreadsheet = $reader->load($inputFileName);
    }

}