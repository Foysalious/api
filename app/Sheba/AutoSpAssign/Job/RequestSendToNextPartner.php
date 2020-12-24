<?php namespace Sheba\AutoSpAssign\Job;

use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Sheba\AutoSpAssign\PartnerOrderRequest\Creator;
use Sheba\AutoSpAssign\PartnerOrderRequest\Store;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\Statuses;
use Sheba\Jobs\JobLogsCreator;
use Sheba\Jobs\JobStatuses;

class RequestSendToNextPartner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:send-order-request {--delay= : Number of seconds to delay command}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order Request send to next partner';
    /** @var Creator $creator */
    private $creator;

    /**
     * RequestSendToNextPartner constructor.
     * @param Creator $creator
     */
    public function __construct(Creator $creator)
    {
        parent::__construct();
        $this->creator = $creator;
    }

    public function handle()
    {
        sleep(intval($this->option('delay')));

        $partner_orders = PartnerOrder::whereHas('jobs', function ($q) {
            $q->notCancelled();
        })->with(['partnerOrderRequests' => function ($q) {
            $q->select('id', 'partner_order_id', 'partner_id', 'status', 'created_at', 'updated_at', 'updated_by_name');
        }, 'jobs'])->where('partner_orders.partner_id', null)->orderBy('partner_orders.id', 'desc')->get();

        $order_request_store = new Store();
        foreach ($partner_orders as $partner_order) {
            $partner_order_requests = $partner_order->partnerOrderRequests;
            if ($this->hasThisStatus($partner_order_requests, Statuses::ACCEPTED)) {
                $this->missAllThePendingRequests($partner_order_requests);
                continue;
            }

            foreach ($partner_order_requests as $partnerOrderRequest) {
                if ($this->shouldIMissTheRequest($partnerOrderRequest)) $this->setRequestToMissed($partnerOrderRequest);
            }

            $sent_to_next_partner = 0;
            if (!$this->hasThisStatus($partner_order_requests, Statuses::PENDING)) {
                if ($partner_ids = $order_request_store->setPartnerOrderId($partner_order->id)->get()) {
                    foreach ($partner_ids as $partner_id) {
                        $order_request = $partner_order_requests->where('partner_id', $partner_id)->first();
                        if ($order_request) continue;
                        $this->creator->setPartnerOrder($partner_order)->setPartners([$partner_id])->create();
                        $sent_to_next_partner = 1;
                        break;
                    }
                }
            }

            if (!$sent_to_next_partner && $this->shouldIChangeJobStatusToNotResponded($partner_order))
                $this->changeJobStatusToNotResponded($partner_order);
        }
    }

    /**
     * @param PartnerOrderRequest $partnerOrderRequest
     * @return bool
     */
    private function shouldIMissTheRequest(PartnerOrderRequest $partnerOrderRequest)
    {
        $time_limit = config('partner.order.request_accept_time_limit_in_seconds');
        return $partnerOrderRequest->status == Statuses::PENDING && $partnerOrderRequest->created_at->addSeconds($time_limit) < Carbon::now();
    }

    /**
     * @param PartnerOrder $partner_order
     * @return bool
     */
    private function shouldIChangeJobStatusToNotResponded(PartnerOrder $partner_order)
    {
        return !$partner_order->partner_id && !$partner_order->partnerOrderRequests->filter(function ($partnerOrderRequest) {
                return in_array($partnerOrderRequest->status, [Statuses::ACCEPTED, Statuses::PENDING]);
            })->count() > 0;
    }

    /**
     * @param $partnerOrderRequests
     * @param $status
     * @return bool
     */
    private function hasThisStatus($partnerOrderRequests, $status)
    {
        return $partnerOrderRequests->filter(function ($partnerOrderRequest) use ($status) {
                return $partnerOrderRequest->status == $status;
            })->count() > 0;
    }

    /**
     * @param $partnerOrderRequests
     */
    private function missAllThePendingRequests($partnerOrderRequests)
    {
        foreach ($partnerOrderRequests as $partnerOrderRequest) {
            if ($partnerOrderRequest->status == Statuses::PENDING) $this->setRequestToMissed($partnerOrderRequest);
        }
    }

    /**
     * @param PartnerOrderRequest $partnerOrderRequest
     */
    private function setRequestToMissed(PartnerOrderRequest $partnerOrderRequest)
    {
        $partnerOrderRequest->status = Statuses::MISSED;
        $partnerOrderRequest->updated_by_name = 'Automatic';
        $partnerOrderRequest->update();
    }

    /**
     * @param PartnerOrder $partnerOrder
     */
    private function changeJobStatusToNotResponded(PartnerOrder $partnerOrder)
    {
        $job = $partnerOrder->jobs->first();
        if (in_array($job->status, [JobStatuses::NOT_RESPONDED, JobStatuses::CANCELLED])) return;

        (new JobLogsCreator($job))->statusChangeLog($job->status, JobStatuses::NOT_RESPONDED);
        $job->status = JobStatuses::NOT_RESPONDED;
        $job->update();
    }
}
