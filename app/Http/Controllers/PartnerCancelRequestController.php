<?php namespace App\Http\Controllers;

use Sheba\Dal\JobCancelReason\JobCancelReason;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Sheba\CancelRequest\PartnerRequestor;
use Sheba\CancelRequest\RequestedByType;
use Sheba\CancelRequest\SendCancelRequest;
use Sheba\CancelRequest\SendCancelRequestJob;
use Sheba\UserRequestInformation;

class PartnerCancelRequestController extends Controller
{
    use  DispatchesJobs;

    public function store($partner, $job, Request $request, PartnerRequestor $partner_requestor, UserRequestInformation $userAgentInformation, SendCancelRequest $send_cancel_request)
    {
        $job = $request->job;
        $reasons = JobCancelReason::where('is_published_for_sp', 1)->pluck('id');
        $reasons = implode(',', array_flatten($reasons));
        $this->validate($request, ['cancel_reason' => "in:$reasons"]);
        $cancel_reason = JobCancelReason::find($request->cancel_reason)->key;
        $partner_requestor->setJob($job);
        $error = $partner_requestor->hasError();
        if ($error) return api_response($request, $error['msg'], $error['code'], ['message' => $error['msg']]);
        $userAgentInformation->setRequest($request);
        $send_cancel_request
            ->setJobId($job->id)
            ->setIsEscalated(false)->setCancelReason($cancel_reason)
            ->setUserAgent($userAgentInformation->getUserAgent())->setPortalName($userAgentInformation->getPortalName())
            ->setIp($userAgentInformation->getIp())
            ->setRequestedById($request->manager_resource->id)
            ->setRequestedByType(RequestedByType::RESOURCE);
        dispatch(new SendCancelRequestJob($send_cancel_request));
        sleep(5);
        return api_response($request, 1, 200, ['message' => "You've successfully submitted the request. Please give some time to process."]);
    }

    public function cancelReasons(Request $request)
    {
        $reasons = JobCancelReason::select('id', 'key', 'name')->where('is_published_for_sp', 1)->get();
        return api_response($request, $reasons, 200, ['reasons' => $reasons]);
    }
}
