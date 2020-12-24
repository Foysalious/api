<?php namespace Sheba\Business\BidStatusChangeLog;

use App\Models\Bid;
use Sheba\Dal\BidStatusChangeLog\BidStatusChangeLogRepositoryInterface;

class Creator
{
    private $bidStatusChangeLogRepository;
    private $data;
    private $bid;
    private $previousStatus;
    private $status;


    public function __construct(BidStatusChangeLogRepositoryInterface $bid_status_change_log_repository)
    {
        $this->bidStatusChangeLogRepository = $bid_status_change_log_repository;
        $this->data = [];
    }

    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
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
        $this->bidStatusChangeLogRepository->create($this->data);
    }

    private function makeData()
    {
        $this->data['bid_id'] = $this->bid->id;
        $this->data['from_status'] = $this->previousStatus;
        $this->data['to_status'] = $this->status;
    }

}