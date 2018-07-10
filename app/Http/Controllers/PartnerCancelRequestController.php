<?php namespace App\Http\Controllers;

use App\Jobs\Job;
use App\Models\JobCancelReason;
use Illuminate\Http\Request;


class PartnerCancelRequestController extends Controller
{
    public function store($job, Request $request)
    {
        try {
            //    dd($request->job);
            return api_response($request, "one", 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function cancelReasons(Request $request)
    {
        try {
            $reasons = JobCancelReason::select('id', 'key', 'name')->where('is_published_for_sp', 1)->get();
            return api_response($request, $reasons, 200, ['reasons' => $reasons]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
