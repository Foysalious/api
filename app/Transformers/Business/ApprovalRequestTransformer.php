<?php namespace App\Transformers\Business;

use App\Models\Profile;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;

class ApprovalRequestTransformer extends TransformerAbstract
{
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

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => $approval_request->status,
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'leave' => [
                'id' => $requestable->id,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i a'),
                'name' => $this->profile->name,
                'type' => $leave_type->title,
                'total_days' => $requestable->total_days,
                'left' => $requestable->left_days,
                'period' => Carbon::parse($requestable->start_date)->format('M d') . ' - ' . Carbon::parse($requestable->end_date)->format('M d'),
                'status' => $requestable->status,
            ]
        ];
    }
}
