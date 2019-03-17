<?php namespace Sheba\Subscription\Customer;

use App\Models\Job;
use App\Models\Order;
use App\Models\Partner;
use App\Models\SubscriptionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Jobs\Changers\StatusChanger;
use Sheba\Jobs\JobStatuses;

class BulkAcceptHandler
{
    /** @var SubscriptionOrder */
    private $subscriptionOrder;
    /** @var Partner */
    private $partner;
    /** @var Collection */
    private $jobs;

    private $orderStatusChanger;

    public function __construct(OrderStatusChanger $order_status_changer)
    {
        $this->orderStatusChanger = $order_status_changer;
    }

    public function setSubscriptionOrder(SubscriptionOrder $order)
    {
        $this->subscriptionOrder = $order;
        $this->partner = $order->partner;
        $this->jobs = $this->subscriptionOrder->orders->map(function (Order $order) {
            return $order->lastJob();
        });
        return $this;
    }

    public function assignAndAccept()
    {
        $this->jobs->each(function (Job $job){
            /** @var StatusChanger $status_changer */
            $status_changer = app(StatusChanger::class);
            $status_changer->setJob($job)->setStatus(JobStatuses::ACCEPTED)->change();
        });

        $this->orderStatusChanger->updateStatus(OrderStatuses::ACCEPTED);
    }

    public function assignAndAcceptApi(Request $request)
    {
        $this->jobs->each(function (Job $job) use ($request){
            /** @var StatusChanger $status_changer */
            $status_changer = app(StatusChanger::class);
            $status_changer->setJob($job)->setRequest($request)->setStatus(JobStatuses::ACCEPTED)->change();
        });
        $this->orderStatusChanger->updateStatus(OrderStatuses::ACCEPTED);
    }
}