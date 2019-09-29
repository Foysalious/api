<?php namespace Sheba\Reports\Complain\Getters;

use Sheba\Dal\Complain\Model as Complain;
use Sheba\Reports\Complain\Query;
use Sheba\Reports\Complain\Presenter;
use Sheba\Repositories\OrderRepository;

class RawDataGetter extends Getter
{
    private $query;
    private $orderRepo;

    public function __construct(OrderRepository $order_repo, Query $query, Presenter $presenter)
    {
        parent::__construct($presenter);
        $this->query = $query;
        $this->orderRepo = $order_repo;
    }

    /**
     * @param Complain $item
     * @return array
     */
    protected function mapForView($item)
    {
        return $this->presenter->setComplain($item)->getForView();
    }

    public function getQuery()
    {
        return $this->query->build();
    }

    public function mapCustomerFirstOrder($data)
    {
        $customer_first_orders = $this->orderRepo->getCustomersFirstOrder($data->pluck('job.partnerOrder.order.customer_id'));
        return $data->map(function ($complain) use ($customer_first_orders) {
            if ($complain->job)
                $complain->job->partnerOrder->order->customer->setFirstOrder($customer_first_orders[$complain->job->partnerOrder->order->customer_id]);
            return $complain;
        });

    }
}