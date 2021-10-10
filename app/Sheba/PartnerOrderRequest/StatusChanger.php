<?php namespace Sheba\PartnerOrderRequest;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Sheba\Order\OrderRequestResend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\Checkout\CommissionCalculator;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Dal\PartnerOrderRequest\Statuses;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Jobs\JobDeliveryChargeCalculator;
use Sheba\Jobs\StatusChanger as JobStatusChanger;

class StatusChanger
{
    use HasErrorCodeAndMessage;

    /** @var PartnerOrderRequestRepositoryInterface $repo */
    private $repo;
    /** @var JobStatusChanger */
    private $jobStatusChanger;
    /** @var PartnerOrderRequest */
    private $partnerOrderRequest;
    private $orderRequestResend;
    /** @var Creator */
    private $creator;
    /** @var Store */
    private $orderRequestStore;
    private $jobDeliveryChargeCalculator;

    public function __construct(JobStatusChanger $job_status_changer, PartnerOrderRequestRepositoryInterface $repo, OrderRequestResend $order_request_resend, Store $order_request_store, Creator $creator,
                                JobDeliveryChargeCalculator $jobDeliveryChargeCalculator)
    {
        $this->jobStatusChanger = $job_status_changer;
        $this->repo = $repo;
        $this->orderRequestResend = $order_request_resend;
        $this->creator = $creator;
        $this->orderRequestStore = $order_request_store;
        $this->jobDeliveryChargeCalculator = $jobDeliveryChargeCalculator;
    }

    public function setPartnerOrderRequest(PartnerOrderRequest $partner_order_request)
    {
        $this->partnerOrderRequest = $partner_order_request;
        return $this;
    }

    public function accept(Request $request)
    {
        $order_request_missed_msg = '';
        /** @var PartnerOrder $partner_order */
        $partner_order = $this->partnerOrderRequest->partnerOrder;

        $accepted_request = $this->repo->getAcceptedRequest($partner_order);
        if ($accepted_request) $order_request_missed_msg = getTimeDifference($accepted_request->updated_at) . " আগে অন্য সার্ভিস প্রোভাইডার অর্ডারটি একসেপ্ট করেছেন। পরের অর্ডারটি পেতে আরও সক্রিয় থাকুন।";

        if ($this->partnerOrderRequest->isNotAcceptable()) {
            $msg = !empty($order_request_missed_msg) ? $order_request_missed_msg : $this->partnerOrderRequest->status . " is not acceptable.";
            $this->setError(403, $msg);
            return;
        }

        if ($this->partnerOrderRequest->created_at->addSeconds(config('partner.order.request_accept_time_limit_in_seconds')) < Carbon::now()) {
            $msg = !empty($order_request_missed_msg) ? $order_request_missed_msg : "Time is over, you Missed it.";
            $this->setError(403, $msg);
            return;
        }

        if ($this->repo->hasAnyAcceptedRequest($partner_order)) {
            $msg = !empty($order_request_missed_msg) ? $order_request_missed_msg : "Someone already did it.";
            $this->setError(403, $msg);
            return;
        }

        /** @var Job $job */
        $job = $partner_order->lastJob();
        $request->merge(['job' => $job]);
        $this->jobStatusChanger->checkForError($request);
        if ($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->jobStatusChanger->getErrorMessage());
            return;
        }
        $this->jobStatusChanger->acceptJobAndAssignResource($request);
        if ($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->jobStatusChanger->getErrorMessage());
            return;
        }
        try {
            DB::transaction(function () use ($request, $partner_order, $job) {
                $this->repo->update($this->partnerOrderRequest, ['status' => Statuses::ACCEPTED]);
                $partner_order->update(['partner_id' => $request->partner->id]);

                $commissions = (new CommissionCalculator())->setCategory($job->category)->setPartner($request->partner);
                $job->update([
                    'commission_rate' => $commissions->getServiceCommission(),
                    'material_commission_rate' => $commissions->getMaterialCommission()
                ]);

                $this->repo->updatePendingRequestsOfOrder($partner_order, ['status' => Statuses::MISSED]);
            });
        } catch (\Exception $e) {
            $this->jobStatusChanger->unacceptJobAndUnAssignResource($request);
        }

    }

    public function decline(Request $request)
    {
        $this->repo->update($this->partnerOrderRequest, ['status' => Statuses::DECLINED]);

        $partner_order = $this->partnerOrderRequest->partnerOrder;
        if (isset($partner_order->partner_id)) {
            return;
        };

        if ($partner_ids = $this->orderRequestStore->setPartnerOrderId($this->partnerOrderRequest->partnerOrder->id)->get()) {
            foreach ($partner_ids as $partner_id) {
                $order_request = $this->partnerOrderRequest->partnerOrder->partnerOrderRequests->where('partner_id', $partner_id)->first();
                if ($order_request) continue;
                $this->creator->setPartnerOrder($this->partnerOrderRequest->partnerOrder)->setPartners([$partner_id])->create();
                return;
            }
        }
        if (!$this->repo->isAllRequestDeclinedOrNotResponded($this->partnerOrderRequest->partnerOrder)) return;
        $request->merge(['job' => $this->partnerOrderRequest->partnerOrder->lastJob()]);
        $this->jobStatusChanger->notResponded($request);
        if ($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->getErrorMessage());
            return;
        }
    }
}
