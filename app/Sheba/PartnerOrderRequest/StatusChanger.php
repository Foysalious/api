<?php namespace Sheba\PartnerOrderRequest;

use Illuminate\Http\Request;
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

    public function __construct(JobStatusChanger $job_status_changer, PartnerOrderRequestRepositoryInterface $repo)
    {
        $this->jobStatusChanger = $job_status_changer;
        $this->repo = $repo;
    }

    public function setPartnerOrderRequest(PartnerOrderRequest $partner_order_request)
    {
        $this->partnerOrderRequest = $partner_order_request;
        return $this;
    }

    public function accept(Request $request)
    {
        if ($this->partnerOrderRequest->isNotAcceptable()) {
            $this->setError(403, $this->partnerOrderRequest->status . " is not acceptable.");
            return;
        }
        if ($this->repo->hasAnyAcceptedRequest($this->partnerOrderRequest->partnerOrder)) {
            $this->setError(403, "Someone already did it.");
            return;
        }

        $request->merge(['job' => $this->partnerOrderRequest->partnerOrder->lastJob()]);
        $this->jobStatusChanger->checkForError($request);
        if ($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->jobStatusChanger->getErrorMessage());
            return;
        }

        $this->repo->update($this->partnerOrderRequest, ['status' => Statuses::ACCEPTED]);
        $this->partnerOrderRequest->partnerOrder->update(['partner_id' => $request->partner->id]);
        $this->jobStatusChanger->acceptJobAndAssignResource($request);

        $this->repo->updatePendingRequestsOfOrder($this->partnerOrderRequest->partner_order, [
            'status' => Statuses::MISSED
        ]);
    }

    public function decline(Request $request)
    {
        $this->repo->update($this->partnerOrderRequest, ['status' => Statuses::DECLINED]);

        if (!$this->repo->isAllRequestDeclinedOrNotResponded($this->partnerOrderRequest->partnerOrder)) return;

        $this->jobStatusChanger->decline($request);
        if ($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->getErrorMessage());
            return;
        }
    }
}
