<?php namespace Sheba\AutoSpAssign;


use App\Models\Customer;
use App\Models\Order;
use Sheba\AutoSpAssign\Sorting\PartnerSort;
use Sheba\AutoSpAssign\Sorting\Strategy\Basic;
use Sheba\AutoSpAssign\Sorting\Strategy\Best;

class Initiator
{
    /** @var Customer */
    private $customer;
    /** @var Order */
    private $order;
    /** @var array */
    private $partnerIds;

    /**
     * @param array $partnerIds
     * @return Initiator
     */
    public function setPartnerIds($partnerIds)
    {
        $this->partnerIds = $partnerIds;
        return $this;
    }

    /**
     * @param Order $order
     * @return Initiator
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function initiate()
    {
        $finder = new Finder();
        $eligible_partners = $finder->setPartnerIds($this->partnerIds)->setCategoryId(14)->find();
        $sorter = new PartnerSort();
        $eligible_partners = $sorter->setStrategy($this->getStrategy())->sort($eligible_partners);

    }

    public function getStrategy()
    {
        if ($this->getCustomerOrderCount() < 3) return new Best();
        return new Basic();
    }

    /**
     * @return mixed
     */
    private function getCustomerOrderCount()
    {
        return Order::where('customer_id', $this->order->customer_id)->select('id')->count();
    }


}