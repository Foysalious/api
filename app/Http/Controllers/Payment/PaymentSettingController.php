<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\OAuth2\AuthUser;
use Sheba\Settings\Payment\PaymentSetting;

class PaymentSettingController extends Controller
{
    public function store(Request $request, PaymentSetting $paymentSetting)
    {
        $this->validate($request, ['method_name' => 'required|in:bkash']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $profile = $auth_user->getProfile();
        if ($profile->getAgreementId($request->method_name)) return api_response($request, null, 403, ['message' => "Already saved"]);
        $response = $paymentSetting->setMethod($request->method_name)->init($profile);
        return http_response($request, $response, 200, ['redirect_url' => $response->getRedirectUrl()]);
    }

}