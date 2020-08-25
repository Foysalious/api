<?php

namespace App\Http\Controllers\NeoBanking;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\NeoBanking\NeoBanking;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getOrganizationInformation($partner, Request $request)
    {
        try {
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $info             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->organizationInformation();
            return api_response($request, $info, 200, ['data' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
