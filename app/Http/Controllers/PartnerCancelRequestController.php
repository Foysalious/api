<?php namespace App\Http\Controllers;

use App\Jobs\Job;
use Illuminate\Http\Request;


class PartnerCancelRequestController extends Controller
{
    public function store($job, Request $request)
    {
//        dd($request->job);
        return api_response($request, "one", 200);
    }
}
