<?php namespace Sheba\Employee;

use App\Models\Business;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Support\Facades\App;
use NumberFormatter;
use Sheba\Dal\Expense\Expense;
use Sheba\Helpers\TimeFrame;

class ExpensePdf
{
    use BusinessBasicInformation;

    private $business;

    /**
     * ExpensePdf constructor.
     * @param Business $business
     */
    public function __construct(Business $business)
    {
        $this->business = app(Business::class);
    }

    public function generate($business_member, $month, $year)
    {
        $business = $this->business->where('id', $business_member->business_id)->select('name', 'logo')->first();
        $member = $business_member->member;
        $profile = $member->profile;
        $role = $business_member->role;

        $time_frame = (new TimeFrame())->forAMonth($month, $year);
        $expenses = Expense::where('business_member_id', $business_member->id)
            ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type', 'created_at')
            ->whereBetween('created_at', $time_frame->getArray())
            ->orderBy('id', 'desc')->get();

        $total = $expenses->sum('amount');
        $total_in_words = (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($total);

        $data = [
            'company' => $business->name,
            'logo' => $this->isDefaultImageByUrl($business->logo) ? null : $business->logo,
            'employee_id' => $business_member->id,
            'employee_name' => $profile->name,
            'employee_mobile' => $profile->mobile,
            'department' => $role->businessDepartment->name,
            'designation' => $role->name,
            'expenses' => $expenses,
            'total_amount' => formatTakaToDecimal($total, true),
            'total_amount_in_words' => ucwords(str_replace('-', ' ', $total_in_words)),
            'month_name' => getMonthName($month, "M") . ", $year"
        ];

        return App::make('dompdf.wrapper')
            ->loadView('pdfs.employee_expense', compact('data'))
            ->download("employee_expense.pdf");
    }
}
