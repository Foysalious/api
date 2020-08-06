<?php namespace App\Transformers\Business;

use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\Model as LeaveModel;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;

class LeaveTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];

    public function transform(LeaveModel $leave)
    {
        $leave_type = $leave->leaveType;
        return [
            'title' => $leave->title,
            'leave_type' => $leave_type->title,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_days' => $leave->total_days,
            'status' => LeaveStatusPresenter::statuses()[$leave->status],
            'requested_on' => $leave->created_at,
            'note' => $leave->note,
        ];
    }

    public function includeAttachments($leave)
    {
        $collection = $this->collection($leave->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}
