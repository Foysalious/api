<?php namespace App\Http\Controllers;

use App\Models\JobCancelReason;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Sheba\CancelRequest\PartnerRequestor;
use Sheba\CancelRequest\SendCancelRequestJob;
use Sheba\ModificationFields;
use Sheba\UserAgentInformation;

class PartnerCancelRequestController extends Controller
{
    use ModificationFields, DispatchesJobs;

    public function store($partner, $job, Request $request, PartnerRequestor $partner_requestor, UserAgentInformation $userAgentInformation)
    {
        $this->setModifier($request->manager_resource);
        $job = $request->job;
        $reasons = JobCancelReason::where('is_published_for_sp', 1)->pluck('id');
        $reasons = implode(',', array_flatten($reasons));
        $this->validate($request, ['cancel_reason' => "in:$reasons"]);
        $cancel_reason = JobCancelReason::find($request->cancel_reason)->key;
        $partner_requestor->setJob($job)->setReason($cancel_reason)->setEscalatedStatus(0);
        $error = $partner_requestor->hasError();
        if ($error) return api_response($request, $error['msg'], $error['code'], ['message' => $error['msg']]);
        $userAgentInformation->setRequest($request);
        dispatch(new SendCancelRequestJob($job, $cancel_reason, null, 0, 0,  $userAgentInformation));
        sleep(5);
        return api_response($request, 1, 200, ['message' => "You've successfully submitted the request. Please give some time to process."]);
    }

    public function cancelReasons(Request $request)
    {
        $reasons = JobCancelReason::select('id', 'key', 'name')->where('is_published_for_sp', 1)->get();
        return api_response($request, $reasons, 200, ['reasons' => $reasons]);
    }
}
