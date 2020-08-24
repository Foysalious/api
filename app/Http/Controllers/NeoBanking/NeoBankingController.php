<?php

namespace App\Http\Controllers\NeoBanking;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getBusinessInformation($partner, Request $request)
    {
        try {
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $info             = "Business info";
            return api_response($request, $info, 200, [
                'data'       => $info
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
