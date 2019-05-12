<?php namespace Sheba\Reports\PartnerOrder\Repositories;

use App\Models\PartnerOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Sheba\Repositories\OrderRepository;

class RawRepository extends Repository
{
    /** @var OrderRepository */
    private $orderRepo;
    /** @var Query */
    private $query;

    public function __construct(OrderRepository $order_repo, Query $query)
    {
        $this->orderRepo = $order_repo;
        $this->query = $query;
    }

    /**
     * @return Collection
     */
    public function get()
    {
        $partner_orders = parent::get();
        $customer_first_orders = $this->orderRepo->getCustomersFirstOrder($partner_orders->pluck('order.customer_id'));
        return $partner_orders->map(function (PartnerOrder $partner_order) use ($customer_first_orders) {
            $partner_order->order->customer->setFirstOrder($customer_first_orders[$partner_order->order->customer_id]);
            return $partner_order;
        });
    }

    /**
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query->build();
    }

    protected function getPartnerIdField()
    {
        return 'partner_id';
    }

    protected function getCancelledDateField()
    {
        return 'cancelled_at';
    }

    protected function getClosedDateField()
    {
        return 'closed_at';
    }
}