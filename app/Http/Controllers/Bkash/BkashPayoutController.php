<?php

namespace App\Http\Controllers\Bkash;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Bkash\Modules\Normal\Methods\Other\SupportingOperation;
use Sheba\Bkash\Modules\Normal\Methods\Payout\NormalPayout;
use Sheba\Bkash\ShebaBkash;

class BkashPayoutController extends Controller
{
    public function pay(Request $request, ShebaBkash $sheba_bkash)
    {
        if ($request->token != 'ShebaAdminPanelToken!@#$!@#') {
            return api_response($request, null, 400);
        }

        try {
            $this->validate($request, ['amount' => 'required|numeric', 'bkash_number' => 'required|string|mobile:bd', 'request_id' => 'required']);
            /** @var NormalPayout $payout */
            $payout = $sheba_bkash->setModule('normal')->getModuleMethod('payout');
            $response = $payout->sendPayment($request->amount, $request->request_id, $request->bkash_number);
            if (!$response) return api_response($request, null, 500, ['message' => 'Intra account transfer failed']);
            else {
                if ($response->hasSuccess()) {
                    return api_response($request, $response->getSuccess(), 200, $response->getSuccess());
                } else {
                    return api_response($request, $response->getError(), 500, $response->getError());
                }
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, null, 400);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $e->getMessage()]);
            $sentry->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }

    public function queryBalance(Request $request, ShebaBkash $sheba_bkash)
    {
        try {
            $this->validate($request, [
                'app_key' => 'required',
                'app_secret' => 'required',
                'username' => 'required',
                'password' => 'required',
            ]);
            /** @var SupportingOperation $support */
            $support = $sheba_bkash->setModule('normal')->getModuleMethod('support');
            $support->setAppKey($request->app_key)->setAppSecret($request->app_secret)
                ->setUsername($request->username)->setPassword($request->password)->setUrl("https://checkout.pay.bka.sh/v1.2.0-beta");
            $result = $support->queryBalance();
            return api_response($request, $result, 200, ['data' => $result->organizationBalance]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, null, 400);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $e->getMessage()]);
            $sentry->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }
}
