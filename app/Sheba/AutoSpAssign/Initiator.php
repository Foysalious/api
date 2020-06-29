<?php namespace Sheba\AutoSpAssign;


use App\Models\Customer;
use App\Models\Order;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Sheba\AutoSpAssign\PartnerOrderRequest\Store;
use Sheba\AutoSpAssign\Sorting\PartnerSort;
use Sheba\AutoSpAssign\Sorting\Strategy\Basic;
use Sheba\AutoSpAssign\Sorting\Strategy\Best;
use Sheba\PartnerOrderRequest\Creator;

class Initiator
{
    /** @var Customer */
    private $customer;
    /** @var PartnerOrder */
    private $partnerOrder;
    /** @var array */
    private $partnerIds;
    /** @var Creator */
    private $partnerOrderRequestCreator;
    /** @var Store */
    private $orderRequestStore;

    public function __construct(Store $order_request_store, Creator $creator)
    {
        $this->orderRequestStore = $order_request_store;
        $this->partnerOrderRequestCreator = $creator;
    }

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
     * @param Customer $customer
     * @return Initiator
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param PartnerOrder $partnerOrder
     * @return Initiator
     */
    public function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        return $this;
    }

    public function initiate()
    {
        if (!$this->canInitiate()) return;
        $finder = new Finder();
        $eligible_partners = $finder->setPartnerIds($this->partnerIds)->setCategoryId($this->partnerOrder->jobs->first()->category_id)->find();
        $sorter = new PartnerSort();
        $eligible_partners = $sorter->setStrategy($this->getStrategy())->sort($eligible_partners);
        $this->orderRequestStore->setPartnerOrderId($this->partnerOrder->id)->setPartners($eligible_partners)->set();
        $first_partner_id = [$eligible_partners[0]->getId()];
        $this->partnerOrderRequestCreator->setPartnerOrder($this->partnerOrder)->setPartners($first_partner_id)->create();
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
        return Order::where('customer_id', $this->partnerOrder->order->customer_id)->select('id')->count();
    }

    private function canInitiate()
    {
        if (count($this->partnerIds) == 0) return false;
        $start = Carbon::parse('2:00 AM');
        $end = Carbon::parse('6:00 AM');
        return Carbon::now()->gte($start) && Carbon::now()->lte($end);
    }

}