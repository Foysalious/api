<?php namespace Sheba\Business\Expense;

use Illuminate\Support\Collection;

class ExpenseList
{
    private $rawExpenseData;

    /**
     * @param Collection $expenses
     * @return $this
     */
    public function setData(Collection $expenses)
    {
        $this->rawExpenseData = $expenses;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $expense_data = [];
        $amount = [];
        $total_food = $total_transport = $total_other = 0;
        $all_department = [];
        $all_employee = [];
        $final_data = [];
        foreach ($this->rawExpenseData as $expense) {
            $business_member = $expense->businessMember;
            $department = $business_member->role ? $business_member->role->businessDepartment->name : null;
            $business_member_id = $expense->business_member_id;
            $expense_amount = floatValFormat($expense->amount);
            if ($department && !in_array($department, $all_department)) $all_department[] = $department;
            if (!in_array($business_member_id, $all_employee)) $all_employee[] = $business_member_id;
            $amount[$business_member_id] = array_key_exists($business_member_id, $amount) ? $amount[$business_member_id] :  0;
            $expense_data[$business_member_id]['member_id'] = $expense->member_id;
            $expense_data[$business_member_id]['business_member_id'] =$business_member_id;
            $expense_data[$business_member_id]['employee_id'] = $business_member->employee_id;
            $expense_data[$business_member_id]['employee_department'] = $department;
            $expense_data[$business_member_id]['created_at'] = $expense->created_at->format('F');
            $expense_data[$business_member_id]['year'] = $expense->year;
            $expense_data[$business_member_id]['month'] = $expense->month;
            $expense_data[$business_member_id]['employee_name'] = $business_member->member->profile->name;
            $expense_data[$business_member_id][$expense->type] = $expense_amount;
            $expense_data[$business_member_id]['amount'] = $amount[$business_member_id] + $expense_amount;
            $amount[$business_member_id] = $expense_data[$business_member_id]['amount'];
            if ($expense->type == 'food') $total_food = $total_food + $expense_amount;
            if ($expense->type == 'transport') $total_transport = $total_transport + $expense_amount;
            if ($expense->type == 'other') $total_other = $total_other + $expense_amount;
        }
        foreach ($expense_data as $data){
            if (!array_key_exists('food', $data)) $expense_data[$data['business_member_id']]['food'] = 0;
            if (!array_key_exists('transport', $data)) $expense_data[$data['business_member_id']]['transport'] = 0;
            if (!array_key_exists('other', $data)) $expense_data[$data['business_member_id']]['other'] = 0;
        }
        $final_data['expense_breakdown'] = $expense_data;
        $final_data['expense_summary'] = [
            "employee" => count($all_employee),
            "department" => count($all_department),
            "transport" => $total_transport,
            "food" => $total_food,
            "other" => $total_other,
            "amount" => $total_transport + $total_food + $total_other
        ];
        return $final_data;
    }
}