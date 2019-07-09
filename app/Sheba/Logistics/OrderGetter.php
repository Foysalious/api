<?php namespace Sheba\Logistics;

use App\Models\Job;
use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\Repository\OrderRepository;

class OrderGetter
{
    /** @var Job */
    private $job;
    /** @var OrderRepository */
    private $repo;

    public function __construct(OrderRepository $order_repo)
    {
        $this->repo = $order_repo;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @throws Exceptions\LogisticServerError
     */
    private function getLogisticOrderDetails()
    {
        $order_id = $this->job->last_logistic_order_id ?: $this->job->first_logistic_order_id;
        if($order_id) return null;
        return $this->repo->find($order_id);
    }

    /**
     * @return Order
     * @throws \Exception
     */
    public function get()
    {
        $data = $this->getLogisticOrderDetails();
        $order = new Order();
        $order->setStatus($data['status'])->setRider($data['rider'])->setId($data['id']);
        return $order;
    }
}