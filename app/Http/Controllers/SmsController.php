<?php namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Sms\Sms;
use Validator;

class SmsController extends Controller
{
    const FROM_BONDHU = 'bondhu';
    const FROM_MARKETPLACE = 'marketplace';
    private $sms;

    public function __construct()
    {
        $this->sms = new Sms();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request)
    {
        try {
            if ($request->server('HTTP_REFERER') != '') {
                $withOutHTTP = explode('//', $request->server('HTTP_REFERER'));
                $trim = explode('/', $withOutHTTP[1]);

                if (
                    $withOutHTTP[0] . '//' . $trim[0] == env('SHEBA_FRONT_END_URL') ||
                    $withOutHTTP[0] . '//' . $trim[0] == env('SHEBA_BONDHU_URL') ||
                    $withOutHTTP[0] . '//' . $trim[0] == env('SHEBA_MARKETPLACE_URL')
                ) {
                    if ($request->has('mobile')) {
                        $mobile = formatMobile(ltrim($request->mobile));
                        $request->merge(['mobile' => $mobile]);
                    }

                    if ($msg = $this->_validateSend($request)) {
                        return api_response($request, null, 500, ['error' => $msg]);
                    }

                    if ($request->action == self::FROM_BONDHU)
                        $sms_text = "Download Sheba Bondhu App https://play.google.com/store/apps/details?id=xyz.sheba.bondhu&hl=en";
                    elseif ($request->action == self::FROM_MARKETPLACE)
                        $sms_text = "Download Sheba MarketPlace App https://play.google.com/store/apps/details?id=xyz.sheba.customersapp&hl=en";

                    $this->sms->shoot($request->mobile, $sms_text);
                    return api_response($request, null, 200);
                }
            }
            return api_response($request, null, 403);
        } catch (Exception $e) {
            return api_response($request, null, 500);
        }
    }

    private function _validateSend($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|mobile:bd', 'action' => 'required|string|in:bondhu,marketplace'
        ], ['mobile' => 'Invalid mobile number!']);

        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
