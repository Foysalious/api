<?php

namespace App\Http\Controllers;


use App\Sheba\PayCharge\Rechargable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\PayCharge\Adapters\PayChargable\RechargeAdapter;
use Sheba\PayCharge\PayCharge;
use Cache;
use DB;

class WalletController extends Controller
{
    public function validatePaycharge(Request $request)
    {
        try {
            $pay_charge = new PayCharge('wallet');
            if ($response = $pay_charge->complete($request->transaction_id)) return api_response($request, null, 200);
            else  return api_response($request, null, 500, ['message' => $pay_charge->message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function recharge(Request $request)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,bkash',
                'amount' => 'required|numeric|min:100',
                'user_id' => 'required',
                'user_type' => 'required|in:customer',
                'remember_token' => 'required'
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

    public function purchase(Request $request)
    {
        try {
            $this->validate($request, [
                'user_id' => 'required',
                'transaction_id' => 'required',
                'user_type' => 'required|in:customer',
                'remember_token' => 'required',
            ]);
            $class_name = "App\\Models\\" . ucwords($request->user_type);
            /** @var Rechargable $user */
            $user = $class_name::where([['id', (int)$request->user_id], ['remember_token', $request->remember_token]])->first();
            if (!$user) return api_response($request, null, 404, ['message' => 'User Not found.']);
            $payment = Cache::store('redis')->get("paycharge::$request->transaction_id");
            $payment = json_decode($payment);
            $pay_chargable = unserialize($payment->pay_chargable);
            if ($pay_chargable->userId == $user->id) {
                DB::transaction(function () use ($pay_chargable, $user, $payment) {
                    $user->debitWallet($pay_chargable->amount);
                    $user->walletTransaction([
                        'amount' => $pay_chargable->amount,
                        'type' => 'Debit', 'log' => 'Service Purchase.',
                        'partner_order_id' => $pay_chargable->id,
                        'transaction_details' => json_encode($payment->method_info)
                    ]);
                });
                return api_response($request, $user, 200);
            } else {
                return api_response($request, $user, 404);
            }
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

    public function getFaqs(Request $request)
    {
        try {
            $faqs = array(
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
                array(
                    'question' => 'Will Service Provider pick me up from my location?',
                    'answer' => 'Yes, Service provider will pick you up from your desired location.'
                ),
            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}