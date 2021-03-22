<?php namespace App\Http\Controllers\Bkash;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Bkash\Modules\Normal\Methods\Other\SupportingOperation;
use Sheba\Bkash\Modules\Normal\Methods\Payout\NormalPayout;
use Sheba\Bkash\ShebaBkash;

use Illuminate\Support\Facades\Redis;
use Throwable;

class BkashPayoutController extends Controller
{
    public function pay(Request $request, ShebaBkash $sheba_bkash)
    {
        return api_response($request, null, 200, ["completed_time" => "2020-12-27T13:46:18:619 GMT+0000",
            "trxID" => "7LR2KRJYNE",
            "status" => "Completed",
            "amount" => 1300,
            "invoice_no" => "7706",
            "receiver_bkash_no" => "01764868959",
            "b2cfee" => 0]);
        if ($request->token != config('sheba.payout_token')) return api_response($request, null, 400);
        try {
            $this->validate($request, ['amount' => 'required|numeric', 'bkash_number' => 'required|string|mobile:bd', 'request_id' => 'required']);
            /** @var NormalPayout $payout */
            $payout = $sheba_bkash->setModule('normal')->getModuleMethod('payout');
            $response = $payout->sendPayment($request->amount, $request->request_id, $request->bkash_number);

            if (!$response) {
                return api_response($request, null, 500, ['message' => 'Intra account transfer failed']);
            } else {
                if ($response->hasSuccess()) {
                    return api_response($request, $response->getSuccess(), 200, $response->getSuccess());
                } else {
                    $error_prefix = 'partner_transaction_failed_';
                    if ($request->has('is_vendor')) {
                        $error_prefix = 'vendor_payout_transaction_failed_';
                    }
                    Redis::set($error_prefix . time(), json_encode($response->getError()));
                    return api_response($request, $response->getError(), 500, $response->getError());
                }
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, null, 400);
        } catch (Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $e->getMessage()]);
            $sentry->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @param ShebaBkash $sheba_bkash
     * @return JsonResponse
     */
    public function queryBalance(Request $request, ShebaBkash $sheba_bkash)
    {
        try {
            $this->validate($request, ['app_key' => 'required', 'app_secret' => 'required', 'username' => 'required', 'password' => 'required',]);
            /** @var SupportingOperation $support */
            $support = $sheba_bkash->setModule('normal')->getModuleMethod('support');
            $support->setAppKey($request->app_key)
                ->setAppSecret($request->app_secret)
                ->setUsername($request->username)
                ->setPassword($request->password)
                ->setUrl("https://checkout.pay.bka.sh/v1.2.0-beta");

            $result = $support->queryBalance();
            if (!isset($result->errorCode))
                return api_response($request, $result, 200, ['data' => $result->organizationBalance]);

            return api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);

            return api_response($request, null, 400);
        } catch (Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $e->getMessage()]);
            $sentry->captureException($e);

            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }

    public function queryPayoutBalance(Request $request, ShebaBkash $shebaBkash)
    {
        $cred = [
            'app_key' => config('bkash.payout.app_key'),
            'app_secret' => config('bkash.payout.app_secret'),
            'username' => config('bkash.payout.username'),
            'password' => config('bkash.payout.password')
        ];
        $request->merge($cred);
        return $this->queryBalance($request, $shebaBkash);
    }
}
