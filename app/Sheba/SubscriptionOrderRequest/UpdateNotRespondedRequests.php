<?php namespace Sheba\SubscriptionOrderRequest;

use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Sheba\Dal\SubscriptionOrder\Statuses as SubscriptionOrderStatuses;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequest;
use Sheba\Dal\SubscriptionOrderRequest\Statuses;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequestRepositoryInterface;

class UpdateNotRespondedRequests extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:update-not-responded-subscription-order-requests';

    /** @var string The console command description. */
    protected $description = 'Change status of not responded partner order requests.';

    /** @var SubscriptionOrderRequestRepositoryInterface */
    private $repo;

    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo)
    {
        $this->repo = $repo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $update_subscription_order_ids = [];
        $q = $this->makeQuery();
        $this->repo->updateByQuery($q, ['status' => Statuses::NOT_RESPONDED]);
        $q->get()->each(function (SubscriptionOrderRequest $request) use (&$update_subscription_order_ids) {
            $is_all_declined_or_not_responded = $request->subscriptionOrder->requests
                ->filter(function (SubscriptionOrderRequest $request) {
                    return !in_array($request->status, [Statuses::DECLINED. Statuses::NOT_RESPONDED]);
                })->count() == 0;

            if($is_all_declined_or_not_responded) {
                $update_subscription_order_ids[] = $request->subscription_order_id;
            }
        });

        SubscriptionOrder::whereIn('id', $update_subscription_order_ids)->update([
            'status' => SubscriptionOrderStatuses::NOT_RESPONDED
        ]);

        $this->info("All good");
    }

    private function makeQuery()
    {
        return SubscriptionOrderRequest::select('id', 'created_at', 'subscription_order_id', 'partner_id')
            ->with('subscriptionOrders.requests', 'partnerOrder.jobs')
            ->where('status', Statuses::PENDING)
            ->where('created_at', '>', Carbon::now()->subMinutes(10));
    }
}
