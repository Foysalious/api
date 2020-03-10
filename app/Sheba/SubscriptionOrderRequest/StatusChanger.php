<?php namespace Sheba\SubscriptionOrderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Dal\SubscriptionOrder\Statuses as SubscriptionOrderStatuses;
use Sheba\Dal\SubscriptionOrderRequest\Statuses;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequest;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequestRepositoryInterface;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Subscription\Customer\OrderStatusChanger;

class StatusChanger
{
    use HasErrorCodeAndMessage;

    /** @var SubscriptionOrderRequestRepositoryInterface $repo */
    private $repo;
    /** @var SubscriptionOrderRequest */
    private $subscriptionOrderRequest;
    /** @var OrderStatusChanger */
    private $subscriptionOrderStatusChanger;

    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo, OrderStatusChanger $subscription_order_status_changer)
    {
        $this->repo = $repo;
        $this->subscriptionOrderStatusChanger = $subscription_order_status_changer;
    }

    public function setSubscriptionOrderRequest(SubscriptionOrderRequest $subscription_order_request)
    {
        $this->subscriptionOrderRequest = $subscription_order_request;
        return $this;
    }

    public function accept(Request $request)
    {
        if ($this->subscriptionOrderRequest->isNotAcceptable()) {
            $this->setError(403, $this->subscriptionOrderRequest->status . " is not acceptable.");
            return;
        }
        if ($this->repo->hasAnyAcceptedRequest($this->subscriptionOrderRequest->subscriptionOrder)) {
            $this->setError(403, "Someone already did it.");
            return;
        }

        DB::transaction(function () use ($request) {
            $subscription_order = $this->subscriptionOrderRequest->subscriptionOrder;
            $subscription_order->update(['partner_id' => $request->partner->id]);
            (new SubscriptionOrderAdapter($subscription_order))->convertToOrder();
            $this->repo->update($this->subscriptionOrderRequest, ['status' => Statuses::ACCEPTED]);
            $this->repo->updatePendingRequestsOfOrder($subscription_order, [
                'status' => Statuses::MISSED
            ]);
        });
    }

    public function decline(Request $request)
    {
        DB::transaction(function () {
            $this->repo->update($this->subscriptionOrderRequest, ['status' => Statuses::DECLINED]);
            if ($this->repo->isAllRequestDeclinedOrNotResponded($this->subscriptionOrderRequest->subscriptionOrder)) {
                $this->subscriptionOrderStatusChanger
                    ->setSubscriptionOrder($this->subscriptionOrderRequest->subscriptionOrder)
                    ->updateStatus(SubscriptionOrderStatuses::DECLINED);
            }
        });
    }
}
