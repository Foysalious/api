<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Sheba\Dal\JobCancelReason\JobCancelReason;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Sheba\CancelRequest\PartnerRequestor;
use Sheba\CancelRequest\RequestedByType;
use Sheba\CancelRequest\SendCancelRequest;
use Sheba\CancelRequest\SendCancelRequestJob;
use Sheba\Jobs\JobStatuses;
use Sheba\UserAgentInformation;

class PartnerCancelRequestController extends Controller
{
    use  DispatchesJobs;

    public function store($partner, $job, Request $request, PartnerRequestor $partner_requestor, UserAgentInformation $userAgentInformation, SendCancelRequest $send_cancel_request)
    {
        $job = $request->job;
        $reasons = JobCancelReason::where('is_published_for_sp', 1)->pluck('id');
        $reasons = implode(',', array_flatten($reasons));
        $this->validate($request, ['cancel_reason' => "in:$reasons"]);
        $cancel_reason = JobCancelReason::find($request->cancel_reason)->key;
        $partner_requestor->setJob($job);
        $error = $partner_requestor->hasError();
        if ($error) return api_response($request, $error['msg'], $error['code'], ['message' => $error['msg']]);
        if ($job->category->preparation_time_minutes != 0) {
            $schedule_date = Carbon::parse($job->schedule_date);
            $preferred_time_split = explode (":", $job->preferred_time_start);
            $schedule_date->hour((int)$preferred_time_split[0]);
            $schedule_date->minute((int)$preferred_time_split[1]);
            $preferred_time_start = $schedule_date->second((int)$preferred_time_split[2]);
            $preparation_time_start = $preferred_time_start ? Carbon::parse($preferred_time_start)->subMinutes($job->category->preparation_time_minutes) : null;
            $now = Carbon::now();
            $between_prep_and_preferred_time = $now->between($preparation_time_start, Carbon::parse($preferred_time_start));
            if ($preparation_time_start && !in_array($job->status, [JobStatuses::PROCESS, JobStatuses::SERVE_DUE]) && ($between_prep_and_preferred_time || $job->status == JobStatuses::SCHEDULE_DUE || (($now > $preferred_time_start) && $job->status == JobStatuses::ACCEPTED))) {
                return api_response($request, null, 400, ['message' => "You cannot request for cancel right before the schedule date and time or when job is in process"]);
            }
        }
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
