<?php namespace Sheba\Business\Expense;

use Illuminate\Support\Collection;

class ExpenseList
{
    private $expenseData;

    /**
     * @param Collection $expenses
     * @return $this
     */
    public function setData(Collection $expenses)
    {
        $this->expenseData = $expenses;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $expense_data = [];

        foreach ($this->expenseData as $expense) {
            $total_amount = 0;
            $expense_summary = [
              'employee_id' => null,
              'member_id' => null,
              'transport' => 0,
              'food' => 0,
              'other' => 0,
              'amount' => 0,
              'year' => null,
              'month' => null,
              'employee_name' => null,
              'employee_department' => null
            ];
            foreach ($expense as $expense_breakdown) {
                $expense_breakdown->amount = floatval($expense_breakdown->amount);
                if (!$expense_summary['member_id']) {
                    $expense_summary['member_id'] = $expense_breakdown->member_id;
                }
                if ($expense_breakdown->type === 'transport') {
                    $expense_summary['transport'] = $expense_breakdown->amount;
                }
                if ($expense_breakdown->type === 'food') {
                    $expense_summary['food'] = $expense_breakdown->amount;
                }
                if ($expense_breakdown->type === 'other') {
                    $expense_summary['other'] = $expense_breakdown->amount;
                }
                if (!$expense_summary['year']) {
                    $expense_summary['year'] = $expense_breakdown->year;
                }
                if (!$expense_summary['month']) {
                    $expense_summary['month'] = $expense_breakdown->created_at->format('F');
                }
                if (!$expense_summary['employee_name']) {
                    $member = $expense_breakdown->member;
                    $business_member = $member->businessMember;
                    $department = $business_member->department();
                    $expense_summary['employee_id'] = $business_member->employee_id;
                    $expense_summary['employee_name'] = $member->profile->name;
                    $expense_summary['employee_department'] = $department ? $department->name : null;
                }
                $total_amount = $total_amount + $expense_breakdown->amount;
            }
            $expense_summary['amount'] = $total_amount;
            array_push($expense_data, $expense_summary);
        }

        return $expense_data;
    }
}