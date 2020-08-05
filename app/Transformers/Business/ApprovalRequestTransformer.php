<?php namespace App\Transformers\Business;

use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;

class ApprovalRequestTransformer extends TransformerAbstract
{
    use BusinessBasicInformation;
    /** @var Profile Profile */
    private $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * @param ApprovalRequest $approval_request
     * @return array
     */
    public function transform(ApprovalRequest $approval_request)
    {
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;

        $leave_type = $requestable->leaveType()->withTrashed()->first();
        $approvers = $this->getApprover($requestable);
        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'leave' => [
                'id' => $requestable->id,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i a'),
                'name' => $this->profile->name,
                'type' => $leave_type->title,
                'total_days' => (int)$requestable->total_days,
                'left' => $requestable->left_days < 0 ? abs($requestable->left_days) : $requestable->left_days,
                'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                'period' => $requestable->start_date->format('M d') . ' - ' . $requestable->end_date->format('M d'),
                'status' => LeaveStatusPresenter::statuses()[$requestable->status],
                'note' => $requestable->note,
                'approvers' => $approvers,
            ]
        ];
    }

    private function getApprover($requestable)
    {
        $approvers = [];
        $requestable->requests->each(function ($approval_request) use (&$approvers) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            $approvers[] = $approval_request->status == Status::ACCEPTED ?
                $profile->name . ' has approved.' : $profile->name . ' not responded yet.';

        });
        return $approvers;
    }
}
