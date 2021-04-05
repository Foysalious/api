<?php namespace App\Sheba\Business\Leave;

use Sheba\Business\Leave\RejectReason\Reason;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Status;

class ApproverWithReason
{
    const SUPER_ADMIN = 1;
    const APPROVER = 0;

    public function getRejectReason($approval_request, $type, $approver_id)
    {
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        $rejection = $requestable->rejection()->where('is_rejected_by_super_admin',$type)->first();
        if (!$rejection) return null;
        if ($type == self::SUPER_ADMIN) return $rejection->note;
        if ($type == self::APPROVER && $approval_request->approver_id != $approver_id || $approval_request->status != Status::REJECTED) return null;
        $reasons = $rejection->reasons;
        $data = [];
        $final_data['note'] = $rejection->note;
        foreach ($reasons as $reason){
            $data['reasons'][] = Reason::getComponents($reason->reason);
        }
        return array_merge($final_data, $data);
    }
}
