<?php namespace Sheba\SubscriptionOrderRequest;


use App\Models\SubscriptionOrder;
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

    public function __construct(Creator $creator, SubscriptionPartnerListBuilder $builder, Director $director, OrderRequestAlgorithm $algorithm, Store $subscriptionOrderRequestStore)
    {
        $this->creator = $creator;
        $this->builder = $builder;
        $this->director = $director;
        $this->algorithm = $algorithm;
        $this->subscriptionOrderRequestStore = $subscriptionOrderRequestStore;
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
        $partners = $this->algorithm->setCustomer($this->subscriptionOrder->customer)->setPartners($partners)->getPartners();
        /* foreach ($partners as $partner) {
            $this->creator->setPartner($partner)->create();
        } */
        $this->subscriptionOrderRequestStore->setSubscriptionOrderId($this->subscriptionOrder->id)->setPartners($partners->pluck('id')->values()->all())->set();
        $first_partner_id = $partners->first()->id;
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
}
