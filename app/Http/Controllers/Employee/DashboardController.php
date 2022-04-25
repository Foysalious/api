<?php namespace App\Http\Controllers\Employee;

use App\Sheba\Business\BusinessBasicInformation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Illuminate\Http\Request;
use App\Models\Business;
use Sheba\Dal\Visit\Status;
use Sheba\Dal\Visit\VisitRepository;
use Sheba\Dal\LiveTrackingSettings\LiveTrackingSettings;

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
        /** @var  LiveTrackingSettings $live_tracking_settings */
        $live_tracking_settings = $business->liveTrackingSettings;

        $is_enable_employee_visit = $business->is_enable_employee_visit;

        $manager = $business ? $business->getActiveBusinessMember()->where('manager_id', $business_member->id)->count() : null;
        $is_manager = $manager ? 1 : 0;

        $dashboard = collect([
            [#0
                'title' => 'Support',
                'target_type' => 'support',
            ],
            [#1
                'title' => 'Attendance',
                'target_type' => 'attendance',
            ],
            [#2
                'title' => 'Notice',
                'target_type' => 'notice',
            ],
            [#3
                'title' => 'Expense',
                'target_type' => 'expense',
            ],
            [#4
                'title' => 'Leave',
                'target_type' => 'leave',
            ],
            [#5
                'title' => 'Approval',
                'target_type' => 'approval',
            ],
            [#6
                'title' => 'Phonebook',
                'target_type' => 'phonebook',
            ],
            [#7
                'title' => 'Payslip',
                'target_type' => 'payslip',

            ],
            [#8
                'title' => 'Visit',
                'target_type' => 'visit',

            ],
            [#9
                'title' => 'Tracking',
                'target_type' => 'tracking',

            ],
            [#10
                'title' => 'My Team',
                'target_type' => 'my_team',

            ],
            [#11
                'title' => 'Feedback',
                'target_type' => 'feedback',
                'link' => "https://sheba.freshdesk.com/support/tickets/new"
            ],
        ]);

        if (!$payroll_setting->is_enable) $dashboard->forget(7);#Payslip
        if (!$is_enable_employee_visit) $dashboard->forget(8);#Visit

        if ($this->isLiveTrackingEnable($live_tracking_settings) || !$is_manager) $dashboard->forget(9);#Tracking
        if (!$is_manager) $dashboard->forget(10);#My Team

        return api_response($request, $dashboard, 200, ['dashboard' => $dashboard->values()]);
    }

    private function isLiveTrackingEnable($live_tracking_settings)
    {
        if (!$live_tracking_settings) {
            return true;
        } elseif ($live_tracking_settings && $live_tracking_settings->is_enable) {
            return true;
        }
    }

    /**
     * @param Request $request
     * @param ApprovalRequestRepositoryInterface $approval_request_repository
     * @param VisitRepository $visit_repository
     * @return JsonResponse
     */
    public function dashboardMenuInfo(Request         $request, ApprovalRequestRepositoryInterface $approval_request_repository,
                                      VisitRepository $visit_repository): JsonResponse
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        /** @var Business $business */
        $business = $this->getBusiness($request);

        $approval_requests = $approval_request_repository->getApprovalRequestByBusinessMember($business_member);
        $pending_approval_requests_count = $approval_request_repository->countPendingLeaveApprovalRequests($business_member);
        $pending_visit = $visit_repository->where('visitor_id', $business_member->id)
            ->whereIn('status', [Status::CREATED, Status::STARTED, Status::REACHED])->where('schedule_date', '<=', Carbon::now());
        $pending_visit_count = $pending_visit->count();
        $manager = $business ? $business->getActiveBusinessMember()->where('manager_id', $business_member->id)->count() : null;
        $is_manager = $manager ? 1 : 0;

        $data = [
            'is_approval_request_required' => $approval_requests->count() > 0 ? 1 : 0,
            'pending_request' => $pending_approval_requests_count,
            'pending_visit_count' => $pending_visit_count,
            'is_manager' => $is_manager
        ];

        return api_response($request, $business_member, 200, ['info' => $data]);
    }
}