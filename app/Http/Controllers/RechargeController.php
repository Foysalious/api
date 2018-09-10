<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\PayCharge\Adapters\RechargeAdapter;
use Sheba\PayCharge\PayCharge;

class RechargeController extends Controller
{

    public function recharge(Request $request)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,bkash',
                'amount' => 'required|numeric|min:100',
                'user_id' => 'required',
                'user_type' => 'required|in:customer',
                'remember_token' => 'required',
            ]);
            $class_name = "App\\Models\\" . ucwords($request->user_type);
            $user = $class_name::where([['id', (int)$request->user_id], ['remember_token', $request->remember_token]])->first();
            if (!$user) return api_response($request, null, 404, ['message' => 'User Not found.']);
            $order_adapter = new RechargeAdapter($user, $request->amount);
            $payment = (new PayCharge($request->payment_method))->init($order_adapter->getPayable());
            return api_response($request, $payment, 200, ['link' => $payment['link'], 'payment' => $payment]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}