<?php namespace App\Transformers\Business;

use App\Models\BusinessRole;
use App\Models\Profile;
use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;

class LeaveRequestDetailsTransformer extends TransformerAbstract
{
    /** @var Profile Profile */
    private $profile;
    private $role;
    protected $defaultIncludes = ['attachments'];

    public function __construct(Profile $profile, BusinessRole $role)
    {
        $this->profile = $profile;
        $this->role = $role;
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

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => $approval_request->status,
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'leave' => [
                'id' => $requestable->id,
                'name' => $this->profile->name,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i a'),
                'type' => $leave_type->title,
                'total_days' => (int)$requestable->total_days,
                'left' => $requestable->left_days < 0 ? abs($requestable->left_days) : $requestable->left_days,
                'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                'period' => $requestable->start_date->format('M d') . ' - ' . $requestable->end_date->format('M d'),
                'note' => $requestable->note,
                'status' => $requestable->status,
            ],
            'department' => [
                'department_id' => $this->role ? $this->role->businessDepartment->id : null,
                'department' => $this->role ? $this->role->businessDepartment->name : null,
                'designation' => $this->role ? $this->role->name : null
            ]
        ];
    }

    public function includeAttachments($approval_request)
    {
        $collection = $this->collection($approval_request->requestable->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}
