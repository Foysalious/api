<?php namespace Sheba\PartnerOrderRequest;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Sheba\Order\OrderRequestResend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Dal\PartnerOrderRequest\Statuses;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Jobs\StatusChanger as JobStatusChanger;

class StatusChanger
{
    use HasErrorCodeAndMessage;

    /** @var PartnerOrderRequestRepositoryInterface $repo */
    private $repo;
    /** @var JobStatusChanger */
    private $jobStatusChanger;

    private $partnerOrderRequest;
    private $orderRequestResend;

    public function __construct(JobStatusChanger $job_status_changer, PartnerOrderRequestRepositoryInterface $repo, OrderRequestResend $order_request_resend)
    {
        $this->jobStatusChanger = $job_status_changer;
        $this->repo = $repo;
        $this->orderRequestResend = $order_request_resend;
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
        if ($accepted_request)
            $order_request_missed_msg = getTimeDifference($accepted_request->updated_at) . " আগে অন্য সার্ভিস প্রোভাইডার অর্ডারটি একসেপ্ট করেছেন। পরের অর্ডারটি পেতে আরও সক্রিয় থাকুন।";

        if ($this->partnerOrderRequest->isNotAcceptable()) {
            $msg = !empty($order_request_missed_msg) ? $order_request_missed_msg : $this->partnerOrderRequest->status . " is not acceptable.";
            $this->setError(403, $msg);
            return;
        }

        if($this->partnerOrderRequest->created_at->addSeconds(config('partner.order.request_accept_timer')) < Carbon::now()){
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
        DB::transaction(function () use ($request, $partner_order, $job) {
            $this->repo->update($this->partnerOrderRequest, ['status' => Statuses::ACCEPTED]);
            $partner_order->update(['partner_id' => $request->partner->id]);
            $job->update(['commission_rate' => $job->category->commission($request->partner->id)]);

            $this->repo->updatePendingRequestsOfOrder($partner_order, ['status' => Statuses::MISSED]);
        });
    }

    public function decline(Request $request)
    {
        $this->repo->update($this->partnerOrderRequest, ['status' => Statuses::DECLINED]);

        if (!$this->repo->isAllRequestDeclinedOrNotResponded($this->partnerOrderRequest->partnerOrder)) return;
        if ($this->partnerOrderRequest->partnerOrder->partner_searched_count == 1) {
            $this->orderRequestResend->setOrder($this->partnerOrderRequest->partnerOrder->order)->send();
            return;
        }
        $request->merge(['job' => $this->partnerOrderRequest->partnerOrder->lastJob()]);
        $this->jobStatusChanger->notResponded($request);
        if ($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->getErrorMessage());
            return;
        }
    }
}
