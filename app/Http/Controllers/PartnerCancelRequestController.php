<?php namespace App\Http\Controllers;

use App\Models\JobCancelReason;

use Illuminate\Http\Request;
use Sheba\CancelRequest\PartnerRequestor;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class PartnerCancelRequestController extends Controller
{
    use ModificationFields;

    public function store($partner, $job, Request $request, PartnerRequestor $partner_requestor)
    {
        $this->setModifier($request->manager_resource);
        $job = $request->job;
        $reasons = JobCancelReason::where('is_published_for_sp', 1)->pluck('id');
        $reasons = implode(',', array_flatten($reasons));
        $this->validate($request, ['cancel_reason' => "in:$reasons"]);
        $partner_requestor->setJob($job)->setReason(JobCancelReason::find($request->cancel_reason)->key)->setEscalatedStatus(0);

        $error = $partner_requestor->hasError($request);
        if ($error) return api_response($request, $error['msg'], $error['code'], ['message' => $error['msg']]);

        $partner_requestor->request();
        return api_response($request, 1, 200, ['message' => "Cancel Request create successfully"]);
    }

    public function cancelReasons(Request $request)
    {
        $reasons = JobCancelReason::select('id', 'key', 'name')->where('is_published_for_sp', 1)->get();
        return api_response($request, $reasons, 200, ['reasons' => $reasons]);
    }
}
