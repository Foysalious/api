<?php namespace App\Http\Controllers\PosRebuild;

use App\Http\Controllers\Controller;
use App\Sheba\PosRebuild\Sms\SmsService;
use Illuminate\Http\Request;


class SmsController extends Controller
{

    public function sendSms(Request $request, SmsService $smsService)
    {
        $this->validate($request,[
           'type' => 'required',
           'type_id' => 'required'
        ]);
        $smsService->setType('type')->setTypeId('type_id')->sendSMS();


    }

}