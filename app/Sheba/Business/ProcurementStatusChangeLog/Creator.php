<?php namespace Sheba\Business\ProcurementStatusChangeLog;

use App\Models\Procurement;
use Sheba\Dal\ProcurementStatusChangeLog\Model as ProcurementStatusChangeLog;
use Sheba\Dal\ProcurementStatusChangeLog\ProcurementStatusChangeLogRepositoryInterface;

class Creator
{
    private $procurementStatusChangeLogRepo;
    private $data;
    private $previousStatus;
    private $procurement;
    private $status;

    public function __construct(ProcurementStatusChangeLogRepositoryInterface $procurement_status_change_log_repo)
    {
        $this->procurementStatusChangeLogRepo = $procurement_status_change_log_repo;
        $this->data = [];
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setPreviousStatus($previous_status)
    {
        $this->previousStatus = $previous_status;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


    public function create()
    {
        $this->makeData();
        $this->procurementStatusChangeLogRepo->create($this->data);
    }

    private function makeData()
    {
        $this->data['procurement_id'] = $this->procurement->id;
        $this->data['from_status'] = $this->previousStatus;
        $this->data['to_status'] = $this->status;
    }
}