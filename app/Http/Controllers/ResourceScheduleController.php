<?php

namespace App\Http\Controllers;


use App\Models\ResourceSchedule;
use Illuminate\Http\Request;

class ResourceScheduleController extends Controller
{
    public function extendTime($resource, $job, Request $request)
    {
        try {
            $resource_schedule = ResourceSchedule::where([['job_id',$job],['resource_id',$resource]])->first();
            if ($resource_schedule == null) {
                return api_response($request, null, 403);
            }
            $resource = $request->resource;
            if (scheduler($resource)->extend($resource_schedule, $request->time)) {
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 403, ['message' => 'Schedule class']);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

}