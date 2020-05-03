<?php namespace App\Transformers\Business;

use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\Model as LeaveModel;
use Sheba\Dal\Leave\Status as LeaveStatus;

class LeaveTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];

    public function transform(LeaveModel $leave)
    {
        return [
            'title' => $leave->title,
            'leave_type' => $leave->leaveType->title,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_days' => $leave->total_days,
            'status' => LeaveStatus::getWithKeys()[strtoupper($leave->status)],
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