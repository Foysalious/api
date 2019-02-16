<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
#use App\Library\Sms;
use Sheba\Sms\Sms;
use Validator;

class SmsController extends Controller
{
    public function send(Request $request)
    {
        try {
            if ($request->server('HTTP_REFERER') != '') {
                $withOutHTTP = explode('//', $request->server('HTTP_REFERER'));
                $trim = explode('/', $withOutHTTP[1]);
                if ($withOutHTTP[0] . '//' . $trim[0] == env('SHEBA_FRONT_END_URL') || $withOutHTTP[0] . '//' . $trim[0] == env('SHEBA_BONDHU_URL')) {
                    if ($request->has('mobile')) {
                        $mobile = formatMobile(ltrim($request->mobile));
                        $request->merge(['mobile' => $mobile]);
                    }
                    if ($msg = $this->_validateSend($request)) {
                        return api_response($request, null, 500, ['error' => $msg]);
                    }
                    (new Sms())->shoot($request->mobile, "Download Sheba Bondhu App https://play.google.com/store/apps/details?id=xyz.sheba.bondhu&hl=en");
                    #Sms::send_single_message($request->mobile, "Download Sheba Bondhu App https://play.google.com/store/apps/details?id=xyz.sheba.bondhu&hl=en");
                    return api_response($request, null, 200);
                }
            }
            return api_response($request, null, 403);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    private function _validateSend($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|mobile:bd',
            'action' => 'required|string|in:bondhu'
        ], ['mobile' => 'Invalid mobile number!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
