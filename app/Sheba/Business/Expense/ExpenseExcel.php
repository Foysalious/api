<?php namespace Sheba\Business\Expense;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseExcel
{
    private $data = [];
    private $expenseData;
    private $name;

    public function setData(array $expense_data)
    {
        $this->expenseData = $expense_data;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        $file_name = $this->name;
        Excel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:H1', function ($cells) {
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
        foreach ($this->expenseData as $expense) {
            array_push($this->data, [
                'month' => Carbon::parse(date('Y-m-0'.$expense['month']))->format('F'),
                'employee_id' => $expense['employee_id'] ?: 'N/A',
                'employee_name' => $expense['employee_name'],
                'employee_department' => $expense['employee_department'],
                'transport' => $expense['transport'],
                'food' => $expense['food'],
                'other' => $expense['other'],
                'amount' => (double)$expense['amount']
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Month', 'Employee ID', 'Employee Name', 'Department', 'Transport', 'Food', 'Other', 'Amount'];
    }
}