<?php

namespace App\Http\Controllers;

use App\Sheba\SmsCampaign\InfoBip\SmsHandler;
use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\SmsCampaign\SmsCampaign;
use Sheba\SmsCampaign\SmsLogs;

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

    public function testInfoBip($partner_id, Requests\SmsCampaignRequest $request, SmsCampaign $campaign)
    {
        try {
            $requests = $request->all();
            if($campaign->formatRequest($requests)->createOrder())
                return api_response($request, null, 200, ['message' => "Campaign created successfully"]);
            else
                return api_response($request, null, 500, ['message' =>'Failed to create campaign', 'code' => 500]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function processQueue(SmsLogs $smsLogs, SmsHandler $smsHandler)
    {
        $smsLogs->processLogs($smsHandler);
    }
}
