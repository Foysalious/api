<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ResourceJobController extends Controller
{
    public function getOngoingJobs($resource, Request $request)
    {
        $resource = $request->resource;
        dd($resource->jobs->where(''));
    }
}
