<?php namespace App\Transformers\Business;

use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use App\Sheba\Business\BusinessBasicInformation;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalFlow\Type;
use App\Models\BusinessMember;
use App\Models\Business;
use App\Models\Profile;

class ApprovalRequestListTransformer extends TransformerAbstract
{
    use BusinessBasicInformation;

    /** @var Business $business */
    private $business;

    /**
     * @param Business $business
     */
    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    /**
     * @param ApprovalRequest $approval_request
     * @return array
     */
    public function transform(ApprovalRequest $approval_request)
    {
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        /** @var BusinessMember $business_member */
        $business_member = $requestable->businessMember;
        /** @var Profile $profile */
        $profile = $business_member->profile();
        $leave_type = $requestable->leaveType()->withTrashed()->first();

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],

            'leave' => [
                'id' => $requestable->id,
                'business_member_id' => $business_member->id,
                'title' => $requestable->title,
                'name' => $profile->name,
                'type' => $leave_type->title,
                'total_days' => $requestable->total_days,
                'is_half_day' => $requestable->is_half_day,
                'half_day_configuration' => $requestable->is_half_day ? [
                    'half_day' => $requestable->half_day_configuration,
                ] : null,
                'leave_date' => ($requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y')) ? $requestable->start_date->format('M d, Y') : $requestable->start_date->format('M d, Y') . ' - ' . $requestable->end_date->format('M d, Y'),
                'status' => LeaveStatusPresenter::statuses()[$requestable->status]
            ]
        ];
    }
}