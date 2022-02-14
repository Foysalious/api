<?php namespace App\Sheba\Business\Expense;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseReportDetailsExcel
{
    private $data = [];
    private $expenses;

    public function setData(Collection $expenses)
    {
        $this->expenses = $expenses;
        return $this;
    }

    public function download()
    {
        $this->makeData();
        Excel::create('Details_Expense_Report', function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:I1', function ($cells) {
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
        foreach ($this->expenses as $expense)
        {
            $this->data[] = [
                'month' => $expense->created_at->format('F'),
                'created_at_date' => $expense->created_at->toDateString(),
                'created_at_time' => $expense->created_at->format('g:i A'),
                'employee_id' => $expense->businessMember->employee_id ?: 'N/A',
                'employee_name' => $expense->businessMember->member->profile->name,
                'employee_department' => $expense->businessMember->role->businessDepartment->name,
                'type' => $expense->type,
                'amount' => (double)$expense->amount,
                'remarks' => $expense->remarks
            ];
        }
    }

    private function getHeaders()
    {
        return ['Month', 'Created At', 'Created Time', 'Employee ID', 'Employee Name', 'Department', 'Expense Type', 'Claimed Amount', 'Remarks'];
    }

}
