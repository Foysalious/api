<?php namespace App\Http\Controllers\Employee;

use App\Sheba\Business\BusinessBasicInformation;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Illuminate\Http\Request;
use App\Models\Business;

class DashboardController extends Controller
{
    use BusinessBasicInformation;

    public function index(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        /** @var Business $business */
        $business = $this->getBusiness($request);
        /** @var PayrollSetting $payroll_setting */
        $payroll_setting = $business->payrollSetting;

        $dashboard_one = [
            [
                'title' => 'Support',
                'target_type' => 'support',
            ],
            [
                'title' => 'Attendance',
                'target_type' => 'attendance',
            ],
            [
                'title' => 'Notice',
                'target_type' => 'notice',
            ],
            [
                'title' => 'Expense',
                'target_type' => 'expense',
            ],
            [
                'title' => 'Leave',
                'target_type' => 'leave',
            ],
            [
                'title' => 'Approval',
                'target_type' => 'approval',
            ],
            [
                'title' => 'Phonebook',
                'target_type' => 'phonebook',
            ],
            [
                'title' => 'Payslip',
                'target_type' => 'payslip',

            ],
            [
                'title' => 'Visit',
                'target_type' => 'visit',

            ],
            [
                'title' => 'Feedback',
                'target_type' => 'feedback',
                'link' => "https://sheba.freshdesk.com/support/tickets/new"
            ],
        ];
        $dashboard_two = [
            [
                'title' => 'Support',
                'target_type' => 'support',
            ],
            [
                'title' => 'Attendance',
                'target_type' => 'attendance',
            ],
            [
                'title' => 'Notice',
                'target_type' => 'notice',
            ],
            [
                'title' => 'Expense',
                'target_type' => 'expense',
            ],
            [
                'title' => 'Leave',
                'target_type' => 'leave',
            ],
            [
                'title' => 'Approval',
                'target_type' => 'approval',
            ],
            [
                'title' => 'Phonebook',
                'target_type' => 'phonebook',
            ],
            [
                'title' => 'Visit',
                'target_type' => 'visit',

            ],
            [
                'title' => 'Feedback',
                'target_type' => 'feedback',
                'link' => "https://sheba.freshdesk.com/support/tickets/new"
            ],
        ];
        $dashboard = $payroll_setting->is_enable ? $dashboard_one : $dashboard_two;
        return api_response($request, $dashboard, 200, ['dashboard' => $dashboard]);
    }
}