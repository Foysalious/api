<?php namespace Sheba\SubscriptionOrderRequest;

use App\Models\Partner;
use App\Models\SubscriptionOrder;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequest;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequestRepositoryInterface;

class Creator
{
    /** @var SubscriptionOrderRequestRepositoryInterface */
    private $repo;
    /** @var SubscriptionOrder */
    private $subscriptionOrder;
    /** @var Partner $partner */
    private $partner;

    /**
     * Creator constructor.
     * @param SubscriptionOrderRequestRepositoryInterface $repo
     */
    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param SubscriptionOrder $subscription_order
     * @return Creator
     */
    public function setSubscriptionOrder(SubscriptionOrder $subscription_order)
    {
        $this->subscriptionOrder = $subscription_order;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return $this
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return SubscriptionOrderRequest
     */
    public function create()
    {
        return $this->repo->create([
            'subscription_order_id' => $this->subscriptionOrder->id,
            'partner_id' => $this->partner->id
        ]);
    }
}
