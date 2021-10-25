<?php namespace Sheba\Business\Leave\Balance;

use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Excel as BalanceExcel;

class Excel
{
    private $balanceData;
    private $leave_types;
    private $excelHandler;
    private $data;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }

    /**
     * @param array $leave_balances
     * @return $this
     */
    public function setBalance(array $leave_balances)
    {
        $this->balanceData = $leave_balances;
        return $this;
    }

    /**
     * @param array $leave_types
     * @return $this
     */
    public function setLeaveType(array $leave_types)
    {
        $leave_type_array = [];
        foreach ($leave_types as $leave_type) {
            $leave_type_array[$leave_type['title']] = 0;
        }

        $this->leave_types = $leave_type_array;
        return $this;
    }

    /**
     * @return void
     * @throws NotAssociativeArray
     */
    public function get()
    {
        $header = $this->getHeaders();
        $this->makeData();
        $file_name = 'Leave_balance_report';
        BalanceExcel::create($file_name, function ($excel) use ($header){
            $excel->sheet('data', function ($sheet) use ($header){
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell('A1:Q1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->balanceData as $balance) {
            foreach ($balance['leave_balance'] as $leave_type) {
                $this->leave_types[$leave_type['title']] =$leave_type['used_leaves'] . '/' . $leave_type['allowed_leaves'];
            }
            $data = ['employee_id' => $balance['employee_id'] ? $balance['employee_id'] : 'N/A', 'employee_name' => $balance['employee_name'], 'department' => $balance['department']] + $this->leave_types;
            array_push($this->data, $data);
        }
    }

    private function getHeaders()
    {
        $header = ['Employee ID', 'Employee Name', 'Department'];
        foreach ($this->leave_types as $key => $leave_types) {
            $header[] = $key;
        }

        return $header;
    }
}
