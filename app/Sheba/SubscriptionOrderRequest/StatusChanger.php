<?php namespace Sheba\SubscriptionOrderRequest;

use Carbon\Carbon;
use GuzzleHttp\Client;
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
    /** @var  Store */
    private $subscriptionOrderRequestStore;
    /** @var Creator */
    private $creator;

    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo, OrderStatusChanger $subscription_order_status_changer, Store $subscription_order_request_store, Creator $creator)
    {
        $this->repo = $repo;
        $this->subscriptionOrderStatusChanger = $subscription_order_status_changer;
        $this->subscriptionOrderRequestStore = $subscription_order_request_store;
        $this->creator = $creator;
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

        if ($this->subscriptionOrderRequest->created_at->addSeconds(config('partner.order.request_accept_time_limit_in_seconds')) < Carbon::now()) {
            $this->setError(403, "Time is over, you Missed it.");
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
        $url = config('sheba.admin_url') . "/api/bulk-accept-subscription-orders/" . $this->subscriptionOrderRequest->subscriptionOrder->id;
        $client = new Client();
        $client->request('POST', $url, ['form_params' => ['remember_token' => $request->manager_resource->remember_token]]);
    }

    public function decline(Request $request)
    {
        DB::transaction(function () {
            $this->repo->update($this->subscriptionOrderRequest, ['status' => Statuses::DECLINED]);
            if ($partner_ids = $this->subscriptionOrderRequestStore->setSubscriptionOrderId($this->subscriptionOrderRequest->subscriptionOrder->id)->get()) {
                $order_requests = $this->subscriptionOrderRequest->subscriptionOrder->subscriptionOrderRequests;
                foreach ($partner_ids as $partner_id) {
                    $order_request = $order_requests->where('partner_id', $partner_id)->first();
                    if ($order_request) continue;
                    $this->creator->setSubscriptionOrder($this->subscriptionOrderRequest->subscriptionOrder)->setPartner($partner_id)->create();
                    return;
                }
            }
            if ($this->repo->isAllRequestDeclinedOrNotResponded($this->subscriptionOrderRequest->subscriptionOrder)) {
                $this->subscriptionOrderStatusChanger
                    ->setSubscriptionOrder($this->subscriptionOrderRequest->subscriptionOrder)
                    ->updateStatus(SubscriptionOrderStatuses::DECLINED);
            }
        });
    }
}
