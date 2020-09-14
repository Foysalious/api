<?php namespace Sheba\SubscriptionOrderRequest;


use App\Models\Order;
use App\Models\SubscriptionOrder;
use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorter;
use Sheba\AutoSpAssign\Sorting\Strategy\Basic;
use Sheba\AutoSpAssign\Sorting\Strategy\Best;
use Sheba\OrderPlace\OrderRequestAlgorithm;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\SubscriptionPartnerListBuilder;

class Generator
{
    private $creator;
    private $builder;
    private $director;
    private $algorithm;

    /** @var SubscriptionOrder */
    private $subscriptionOrder;

    /** @var Store */
    private $subscriptionOrderRequestStore;
    /** @var Sorter */
    private $sorter;

    public function __construct(Creator $creator, SubscriptionPartnerListBuilder $builder, Director $director, OrderRequestAlgorithm $algorithm, Store $subscriptionOrderRequestStore, Sorter $sorter)
    {
        $this->creator = $creator;
        $this->builder = $builder;
        $this->director = $director;
        $this->algorithm = $algorithm;
        $this->subscriptionOrderRequestStore = $subscriptionOrderRequestStore;
        $this->sorter = $sorter;
    }

    public function setSubscriptionOrder(SubscriptionOrder $subscription_order)
    {
        $this->subscriptionOrder = $subscription_order;
        $this->creator->setSubscriptionOrder($subscription_order);
        return $this;
    }

    public function generate()
    {
        $partners = $this->fetchPartner();
        if (count($partners) == 0) return;
        $eligible_partners = $this->sorter->setStrategy($this->getStrategy())->setPartnerIds($partners->pluck('id')->values()->all())
            ->setCategoryId($this->subscriptionOrder->category_id)->getSortedPartners();
        $this->subscriptionOrderRequestStore->setSubscriptionOrderId($this->subscriptionOrder->id)
            ->setPartners($this->getAscendingSortedPartnerIds($eligible_partners))->set();
        $first_partner_id = $eligible_partners[0]->getId();
        $this->creator->setPartner($first_partner_id)->create();
    }

    private function fetchPartner()
    {
        $this->builder->setGeo($this->subscriptionOrder->deliveryAddress->getGeo())
            ->setServiceRequestObjectArray($this->subscriptionOrder->getServiceRequestObjects())
            ->setScheduleTime($this->subscriptionOrder->getScheduleTime())
            ->setScheduleDate($this->subscriptionOrder->getScheduleDates())
            ->setCycleType($this->subscriptionOrder->billing_cycle);
        $this->director->setBuilder($this->builder)->buildPartnerListForOrderPlacement();
        return $this->builder->get();
    }

    public function getStrategy()
    {
        if ($this->getCustomerOrderCount() <= 3) return new Best();
        return new Basic();
    }

    /**
     * @return mixed
     */
    private function getCustomerOrderCount()
    {
        return Order::where('customer_id', $this->subscriptionOrder->customer_id)->select('id')->count();
    }

    /**
     * @param EligiblePartner[] $eligible_partners
     * @return array
     */
    private function getAscendingSortedPartnerIds($eligible_partners)
    {
        $data = [];
        foreach ($eligible_partners as $eligible_partner) {
            array_push($data, $eligible_partner->getId());
        }
        return $data;
    }
}
