<?php

namespace App\Http\Controllers\Bkash;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Bkash\ShebaBkash;

class BkashPayoutController extends Controller
{
    public function pay(Request $request, ShebaBkash $shebaBkash)
    {
        /** @var \Sheba\Bkash\Modules\Normal\Methods\Payout\NormalPayout $payout */
        if ($request->token != 'ShebaAdminPanelToken!@#$!@#') {
            return api_response($request, null, 400);
        }

        try {
            $this->validate($request, ['amount' => 'required|numeric', 'bkash_number' => 'required|string|mobile:bd', 'request_id' => 'required']);
            $payout = $shebaBkash->setModule('normal')->getModuleMethod('payout');
            $response = $payout->sendPayment($request->amount, $request->request_id, $request->bkash_number);
            if ($response->hasSuccess()) {
                return api_response($request, $response->getSuccess(), 200, $response->getSuccess());
            } else {
                return api_response($request, $response->getError(), 500, $response->getError());
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        } catch (\Throwable $e) {
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }
}
