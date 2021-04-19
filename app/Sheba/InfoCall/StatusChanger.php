<?php namespace Sheba\InfoCall;

use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\InfoCall\Statuses;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLogRepository;
use Sheba\ModificationFields;

class StatusChanger
{
    use ModificationFields;
    /** @var InfoCallStatusLogRepository  */
    private $statusLogRepo;
    /** @var InfoCall */
    private $infoCall;
    private $status;
    private $rejectReason;
    private $rejectReasonDetails;
    public function __construct(InfoCallStatusLogRepository $status_log_repo)
    {
        $this->statusLogRepo = $status_log_repo;
    }

    public function setInfoCall(InfoCall $info_call)
    {
        $this->infoCall = $info_call;
        return $this;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $rejectReason
     */
    public function setRejectReason($rejectReason)
    {
        $this->rejectReason = $rejectReason;
        return $this;
    }

    /**
     * @param mixed $rejectReasonDetails
     */
    public function setRejectReasonDetails($rejectReasonDetails)
    {
        $this->rejectReasonDetails = $rejectReasonDetails;
        return $this;
    }


    public function change()
    {
        $data['status'] = $this->status;
        $old_status = $this->infoCall->status;
        $this->infoCall->update($this->withUpdateModificationField($data));
        $log_data = [
            'info_call_id'=>$this->infoCall->id,
            'from'=> $old_status,
            'to' => $this->status
        ];
        if($this->status == Statuses::REJECTED) {
            $log_data['reject_reason_id'] = (int) $this->rejectReason;
            $log_data['reject_reason_details'] = $this->rejectReasonDetails;
        }
        $this->statusLogRepo->create($log_data);
    }


}