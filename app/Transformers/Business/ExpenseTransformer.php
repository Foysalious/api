<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\Expense\Expense;

class ExpenseTransformer extends TransformerAbstract
{
    /**
     * @param Expense $expense
     * @return array
     */
    public function transform(Expense $expense)
    {
        $member = $expense->member;
        $business_member = $member->businessMember;
        $department = $business_member->department();

        return [
            "id" => $expense->id,
            "employee_id"=>$business_member->employee_id,
            "member_id" => $member->id,
            "amount" => $expense->amount,
            "created_at" => $expense->created_at->format('Y-m-d h:i:s'),
            "year" => $expense->year,
            "month" => $expense->month,
            'employee_name' => $member->profile->name,
            'employee_department' => $department ? $department->name : null
        ];
    }
}
