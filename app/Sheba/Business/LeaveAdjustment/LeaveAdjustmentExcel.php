<?php namespace Sheba\Business\LeaveAdjustment;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveAdjustmentExcel
{
    /** @var Spreadsheet */
    private $spreadsheet;
    /** @var Worksheet */
    private $worksheet;

    private $leaveDataCurrentRow = 0;
    private $adminDataCurrentRow = 0;

    public static function load($file): LeaveAdjustmentExcel
    {
        return new LeaveAdjustmentExcel((new Xlsx())->load($file));
    }

    public function __construct(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
        $this->worksheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addSuperAdmin($id, $name)
    {
        $this->worksheet
            ->getCell(AdjustmentExcel::superAdminIdCell($this->adminDataCurrentRow))
            ->setValue($id);

        $this->worksheet
            ->getCell(AdjustmentExcel::superAdminNameCell($this->adminDataCurrentRow))
            ->setValue($name);

        $this->adminDataCurrentRow++;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addLeave($id, $title, $total_days)
    {
        $this->worksheet
            ->getCell(AdjustmentExcel::leaveTypeIdCell($this->leaveDataCurrentRow))
            ->setValue($id);

        $this->worksheet
            ->getCell(AdjustmentExcel::leaveTypeTitleCell($this->leaveDataCurrentRow))
            ->setValue($title);


        $this->worksheet
            ->getCell(AdjustmentExcel::totalDaysCell($this->leaveDataCurrentRow))
            ->setValue($total_days);

        $this->leaveDataCurrentRow++;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save($file)
    {
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet))->save($file);
    }
}
