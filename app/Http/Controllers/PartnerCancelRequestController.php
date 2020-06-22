<?php namespace App\Http\Controllers;

use App\Jobs\SendCancelRequest;
use App\Models\JobCancelReason;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Sheba\CancelRequest\PartnerRequestor;
use Sheba\ModificationFields;

class PartnerCancelRequestController extends Controller
{
    use ModificationFields, DispatchesJobs;

    public function store($partner, $job, Request $request, PartnerRequestor $partner_requestor)
    {
        $this->setModifier($request->manager_resource);
        $job = $request->job;
        $reasons = JobCancelReason::where('is_published_for_sp', 1)->pluck('id');
        $reasons = implode(',', array_flatten($reasons));
        $this->validate($request, ['cancel_reason' => "in:$reasons"]);
        $cancel_reason = JobCancelReason::find($request->cancel_reason)->key;
        $partner_requestor->setJob($job)->setReason(JobCancelReason::find($request->cancel_reason)->key)->setEscalatedStatus(0);
        $error = $partner_requestor->hasError();
        if ($error) return api_response($request, $error['msg'], $error['code'], ['message' => $error['msg']]);
        dispatch(new SendCancelRequest($job, $cancel_reason, 0, 0));
        return api_response($request, 1, 200, ['message' => "You've successfully submitted the request. Please give some time to process."]);
    }

    public function cancelReasons(Request $request)
    {
        $reasons = JobCancelReason::select('id', 'key', 'name')->where('is_published_for_sp', 1)->get();
        return api_response($request, $reasons, 200, ['reasons' => $reasons]);
    }
}
