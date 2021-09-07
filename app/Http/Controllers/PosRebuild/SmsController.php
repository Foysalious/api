<?php namespace App\Http\Controllers\PosRebuild;

use App\Http\Controllers\Controller;
use App\Sheba\PosRebuild\Sms\SmsService;
use App\Sheba\PosRebuild\Sms\Types;
use Illuminate\Http\Request;


class SmsController extends Controller
{
    public function sendSms(Request $request, SmsService $smsService)
    {
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', Types::get()),
            'type_id' => 'required'
        ]);
        $partner = $request->auth_user->getPartner();
        $smsService->setPartner($partner)->setType($request->type)->setTypeId($request->type_id)->sendSMS();
        return http_response($request, null, 200, ['message' => 'Sms sent successfully']);
    }
}