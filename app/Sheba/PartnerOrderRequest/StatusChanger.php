<?php namespace Sheba\PartnerOrderRequest;

use Illuminate\Http\Request;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Jobs\StatusChanger as JobStatusChanger;

class StatusChanger
{
    use HasErrorCodeAndMessage;

    /** @var PartnerOrderRequestRepositoryInterface $repo */
    private $repo;
    /** @var JobStatusChanger */
    private $jobStatusChanger;

    public function __construct(JobStatusChanger $job_status_changer, PartnerOrderRequestRepositoryInterface $repo)
    {
        $this->jobStatusChanger = $job_status_changer;
        $this->repo = $repo;
    }

    public function accept(Request $request)
    {
        $partner_order_request = $request->partner_order_request;
        if($partner_order_request->isNotAcceptable()) {
            $this->setError(403, $partner_order_request->status . " is not acceptable.");
            return;
        }
        if($this->repo->hasAnyAcceptedRequest($partner_order_request)) {
            $this->setError(403, "Someone already did it.");
        }
        $this->repo->update($partner_order_request, [
            'status' => 'accepted'
        ]);
        $partner_order_request->partner_order->update();

        $this->jobStatusChanger->acceptJobAndAssignResource($request);
        if($this->jobStatusChanger->hasError()) {
            $this->setError($this->jobStatusChanger->getErrorCode(), $this->getErrorMessage());
        }
        $this->repo->updatePendingRequestsOfOrder($partner_order_request->partner_order, [
            'status' => 'missed'
        ]);
    }
}
