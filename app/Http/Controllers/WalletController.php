<?php

namespace App\Http\Controllers;


use App\Models\PartnerOrder;
use App\Sheba\PayCharge\Rechargable;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\PayCharge\Adapters\PayChargable\RechargeAdapter;
use Sheba\PayCharge\PayCharge;
use Cache;
use DB;
use Sheba\ShebaBonusCredit;

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
                'amount' => 'required|numeric|min:10|max:5000',
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
                if ($user->shebaCredit() < $pay_chargable->amount) return api_response($request, null, 400, ['message' => 'You don\'t have sufficient credit']);
                try {
                    DB::transaction(function () use ($pay_chargable, $user, $payment) {
                        $remaining = (new ShebaBonusCredit())->setUser($user)->setSpentModel(PartnerOrder::find($pay_chargable->id))->deduct($pay_chargable->amount);
                        if ($remaining > 0) {
                            $user->debitWallet($remaining);
                            $user->walletTransaction([
                                'amount' => $remaining,
                                'type' => 'Debit', 'log' => 'Service Purchase.',
                                'partner_order_id' => $pay_chargable->id,
                                'transaction_details' => json_encode($payment->method_info),
                                'created_at' => Carbon::now()
                            ]);
                        }
                    });
                } catch (QueryException $e) {
                    app('sentry')->captureException($e);
                    return api_response($request, null, 500);
                }
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
                    'question' => '1. How can I purchase Sheba Credit?',
                    'answer' => 'You can purchase your Sheba Credit via your credit/debit card, mobile banking services like bKash/rocket. You will be asked to give proper information to confirm the payment. After completing the purchase, you will get a confirmation screen in the app.'
                ),
                array(
                    'question' => '2. Where I can use my Sheba Credit?',
                    'answer' => 'Currently you can use your Sheba Credit while placing order to avail any sheba services. Soon you can use your Sheba Credit in many places.'
                ),
                array(
                    'question' => '3. Where I can check my current credit balance?',
                    'answer' => 'You can check your credit balance by selecting credit from bottom menu and your current credit balance will be displayed there. In web you can check Sheba Credit balance from your profile.'
                ),
                array(
                    'question' => '4. What is the value of each credit?',
                    'answer' => 'The value of each Sheba Credit is 1 BDT. '
                ),
                array(
                    'question' => '5. What is the minimum and maximum credit I can purchase?',
                    'answer' => 'The minimum Sheba Credit is 10 that you can purchase and the maximum Sheba Credit is 5000.'
                ),
                array(
                    'question' => '6. What benefits I will get using Sheba Credit?',
                    'answer' => 'You can go with hassle free payment transaction while purchasing any sheba service & you will get instant bonus Sheba Credit if you purchase any service using Sheba Credit. Soon you will get more benefits from Sheba Credit.'
                ),
                array(
                    'question' => '7. Where I can check my credit transactions?',
                    'answer' => 'You can check all of your credit transactions history with details in history page.'
                ),
                array(
                    'question' => '8. What if I completed purchase but my credit balance doesn’t update?',
                    'answer' => 'For this type of issue, please send us a mail to info@sheba.xyz. After a few verifications, sheba.xyz will adjust the Sheba Credit balance.'
                ),
                array(
                    'question' => '9. Is there any hidden charge?',
                    'answer' => 'Sheba Credit is completely free. There is no hidden charge. You don’t need to pay any additional charges to purchase or purchase any services.'
                )
            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}