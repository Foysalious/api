<?php namespace Sheba\Employee;

use Illuminate\Support\Facades\App;
use NumberFormatter;
use Sheba\Dal\Expense\Expense;
use Sheba\Helpers\TimeFrame;

class ExpensePdf
{
    public function generate($business_member, $month, $year)
    {
        $role = $business_member->role;
        $time_frame = (new TimeFrame())->forAMonth($month, $year);

        $expenses = Expense::where('member_id',$business_member->member->id)
            ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type', 'created_at')
            ->whereBetween('created_at', $time_frame->getArray())
            ->orderBy('id', 'desc')->get();

        $total = $expenses->sum('amount');
        $total_in_words = (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($total);

        $data = [
            'employee_id' => $business_member->id,
            'employee_name' => $business_member->member->profile->name,
            'employee_mobile' => $business_member->member->profile->mobile,
            'department' => $role->businessDepartment->name,
            'designation' => $role->name,
            'expenses' => $expenses,
            'total_amount' => formatTakaToDecimal($total, true),
            'total_amount_in_words' => ucwords(str_replace('-', ' ', $total_in_words)),
            'month_name' =>  getMonthName($month, "M") . ", $year"
        ];

        // return view('pdfs.employee_expense', compact('data'));
        return App::make('dompdf.wrapper')
            ->loadView('pdfs.employee_expense', compact('data'))
            ->download("employee_expense.pdf");
    }
}
