<?php namespace Sheba\Pos\Log;

use App\Models\PosOrder;
use Sheba\Pos\Repositories\PosOrderLogRepository;

class Creator
{
    /** @var PosOrder $order*/
    private $order;
    private $type;
    private $log;
    private $details;
    /** @var PosOrderLogRepository */
    private $repo;

    public function __construct(PosOrderLogRepository $repo)
    {
        $this->repo = $repo;
    }

    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed $type
     * @return Creator
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $log
     * @return Creator
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @param mixed $details
     * @return Creator
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    public function create()
    {
        $data = [
            'type' => $this->type,
            'pos_order_id' => $this->order->id,
            'log' => $this->log,
            'details' => $this->details
        ];

        return $this->repo->save($data);
    }
}