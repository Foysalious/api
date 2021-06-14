<?php namespace App\Http\Controllers;

use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Sms\Sms;
use Validator;

class SmsController extends Controller
{
    const FROM_BONDHU = 'bondhu';
    const FROM_MARKETPLACE = 'marketplace';
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request)
    {
        if (!$this->isFromValidWebsite($request)) return api_response($request, null, 403);

        if ($request->has('mobile')) {
            $mobile = formatMobile(ltrim($request->mobile));
            $request->merge(['mobile' => $mobile]);
        }

        if ($msg = $this->_validateSend($request)) {
            return api_response($request, null, 500, ['error' => $msg]);
        }

        $business_type = null;

        if ($request->action == self::FROM_BONDHU) {
            $business_type = BusinessType::BONDHU;
            $sms_text = "Download Sheba Bondhu App https://play.google.com/store/apps/details?id=xyz.sheba.bondhu&hl=en";
        }
        elseif ($request->action == self::FROM_MARKETPLACE) {
            $business_type = BusinessType::MARKETPLACE;
            $sms_text = "Download Sheba MarketPlace App https://play.google.com/store/apps/details?id=xyz.sheba.customersapp&hl=en";
        }

        $this->sms
            ->setFeatureType(FeatureType::MARKETING)
            ->setBusinessType($business_type)
            ->shoot($request->mobile, $sms_text);
        return api_response($request, null, 200);
    }

    private function _validateSend($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|mobile:bd',
            'action' => 'required|string|in:bondhu,marketplace'
        ], ['mobile' => 'Invalid mobile number!']);

        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function isFromValidWebsite(Request $request)
    {
        if ($request->server('HTTP_REFERER') == '') return false;

        $without_HTTP = explode('//', $request->server('HTTP_REFERER'));
        $trim = explode('/', $without_HTTP[1]);

        return
            $without_HTTP[0] . '//' . $trim[0] == env('SHEBA_FRONT_END_URL') ||
            $without_HTTP[0] . '//' . $trim[0] == env('SHEBA_BONDHU_URL') ||
            $without_HTTP[0] . '//' . $trim[0] == env('SHEBA_MARKETPLACE_URL');
    }
}
