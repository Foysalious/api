<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\Sms\InfoBip;

class SmsCampaignOrderController extends Controller
{
    public function getSettings(Request $request)
    {
        try{
            return api_response($request, null, 200, ['settings' => constants('SMS_CAMPAIGN')]);
        }   catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function testInfoBip(Requests\SmsCampaignRequest $request)
    {

    }
}
