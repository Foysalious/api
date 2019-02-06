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

    public function testInfoBip(InfoBip $infoBip)
    {
//        dd($infoBip->get('/sms/2/logs',['messageId' => ['1549429193082862573','1549432543288106571']]));
        dd($infoBip->post('/sms/2/text/single',[
            'from' => 'Sheba.xyz',
            'to' => [
                '8801869715616',
                '8801678242962'
            ],
            'text' => 'test sms too them'
        ]));
    }
}
