<?php

namespace App\Http\Controllers;


use App\Models\Job;
use App\Models\ResourceSchedule;
use App\Repositories\PushNotificationRepository;
use Illuminate\Http\Request;

class ResourceScheduleController extends Controller
{
    public function extendTime($resource, $job, Request $request)
    {
        try {
            $resource_schedule = ResourceSchedule::where([['job_id', $job], ['resource_id', $resource]])->first();
            if ($resource_schedule == null) {
                return api_response($request, null, 403);
            }
            $resource = $request->resource;
            if (scheduler($resource)->extend($resource_schedule, $request->time)) {
                $this->sendNotificationToPartner($request->job);
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 403, ['message' => 'Schedule class']);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function sendNotificationToPartner(Job $job)
    {
        (new PushNotificationRepository())->send([
            "title" => 'Resource extended time',
            "message" => $job->resource->profile->name . " has extended time for " . $job->fullCode(),
            "event_type" => 'PartnerOrder',
            "event_id" => "$job->partner_order->id",
            "action" => 'extend_time'
        ], env('MANAGER_TOPIC_NAME') . $job->partner_order->partner_id);
    }

}