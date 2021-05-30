<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Status;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Helpers\TimeFrame;

class LeaveBalanceDetailsTransformer extends TransformerAbstract
{
    private $leave_types;
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    private $leaveLogRepo;

    /**
     * LeaveBalanceDetailsTransformer constructor.
     * @param $leave_types
     * @param TimeFrame $time_frame
     * @param LeaveLogRepo $leave_log_repo
     */
    public function __construct($leave_types, TimeFrame $time_frame, LeaveLogRepo $leave_log_repo)
    {
        $this->leave_types = $leave_types;
        $this->timeFrame = $time_frame;
        $this->leaveLogRepo = $leave_log_repo;
    }

    /**
     * @param $business_member
     * @return array
     */
    public function transform($business_member)
    {

        $this->businessMember = $business_member;
        $leavesInCurrentFiscalYear = $this->businessMember->getCurrentFiscalYearLeaves();
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $business_member->role;
        /** @var Leave $leaves */
        $leaves = $leavesInCurrentFiscalYear;
        /** @var Business $business */
        $business = $this->businessMember->business;

        $leaves_approved_count = $leaves->where('status', Status::ACCEPTED)->count();
        $leaves_rejected_count = $leaves->where('status', Status::REJECTED)->count();
        list($leaves, $leave_logs) = $this->formatLeavesAndLogs($leaves);
        return [
            'employee_name' => $profile->name,
            'employee_pro_pic' => $profile->pro_pic,
            'employee_mobile' => $profile->mobile,
            'employee_id' => $business_member->employee_id,
            'join_date' => $business_member->join_date ? $business_member->join_date->format('F Y') : 'n/s',
            'designation' => $role ? $role->name : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'company' => $business->name,
            'logo' => $business->logo,
            'approved_count' => $leaves_approved_count,
            'rejected_count' => $leaves_rejected_count,
            'leave_balance' => $this->calculate(),
            'leaves' => $leaves,
            'leave_logs' => $leave_logs
        ];
    }

    /**
     * @return array
     */
    private function calculate()
    {
        $single_employee_leave_balance = [];
        foreach ($this->leave_types as $leave_type) {
            if ($leave_type->trashed()) continue;
            $used_leave_days = $this->businessMember->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type->id);
            $leave_type_total_days = $this->businessMember->getTotalLeaveDaysByLeaveTypes($leave_type->id);
            array_push($single_employee_leave_balance, [
                'title' => $leave_type->title,
                'allowed_leaves' => (int)$leave_type_total_days,
                'used_leaves' => $used_leave_days,
                'is_leave_days_exceeded' => ($used_leave_days > (int)$leave_type_total_days)
            ]);
        }

        return $single_employee_leave_balance;
    }

    /**
     * @param $leaves
     * @return array
     */
    private function formatLeavesAndLogs($leaves)
    {
        $requested_business_member_id = request()->business_member->id;
        $all_leaves = [];
        $all_leave_logs = [];
        foreach ($leaves as $leave) {
            $get_current_login_user_leave_request = $leave->requests->where('approver_id', $requested_business_member_id)->first();
            array_push($all_leaves, [
                'id' => $leave->id,
                'date' => ($leave->start_date->format('M d, Y') == $leave->end_date->format('M d, Y')) ? $leave->start_date->format('M d') : $leave->start_date->format('M d') . ' - ' . $leave->end_date->format('M d'),
                'leave_type' => $leave->title,
                'leave_days' => (double)$leave->total_days,
                'status' => LeaveStatusPresenter::statuses()[$leave->status],
                'approval_request_status' => $get_current_login_user_leave_request ?
                    ApprovalRequestPresenter::statuses()[$get_current_login_user_leave_request->status] : 'N/A',
                'request' => [
                    'has_access' => $get_current_login_user_leave_request ? true : false,
                    'id' => $get_current_login_user_leave_request ? $get_current_login_user_leave_request->id : null
                ]
            ]);

            $logs = $this->leaveLogRepo->where('leave_id', $leave->id)->where('type', 'leave_adjustment')->where('is_changed_by_super', 0)->select('log', 'created_at')->get();
            if (!$logs->isEmpty()) {
                $logs->map(function ($log) use (&$all_leave_logs) {
                    array_push($all_leave_logs, [
                        'log' => $log->log,
                        'created_at' => $log->created_at->format('h:i A - d M, Y')
                    ]);
                });
            }
        }

        return [$all_leaves, $all_leave_logs];
    }
}
