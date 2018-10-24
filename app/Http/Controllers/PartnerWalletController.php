<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PartnerOrder;
use App\Models\Payment;

use App\Repositories\PaymentRepository;
use Carbon\Carbon;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\Payment\ShebaPayment;
use DB;
use Sheba\ShebaBonusCredit;

class PartnerWalletController extends Controller
{
    public function validatePayment(Request $request)
    {
        try {
            /** @var Payment $payment */
            $payment = Payment::where('transaction_id', $request->transaction_id)->valid()->first();

            if (!$payment) return api_response($request, null, 404);
            elseif ($payment->isComplete()) return api_response($request, 1, 200, ['message' => 'Payment completed']);
            elseif (!$payment->canComplete()) return api_response($request, null, 400, ['message' => 'Payment validation failed.']);

            $sheba_payment = new ShebaPayment('partner_wallet');
            $payment = $sheba_payment->complete($payment);

            if ($payment->isComplete()) $message = 'Payment successfully completed';
            elseif ($payment->isPassed()) $message = 'Your payment has been received but there was a system error. It will take some time to transaction your order. Call 16516 for support.';

            return api_response($request, null, 200, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function purchase(Request $request, PaymentRepository $paymentRepository)
    {
        try {
            $this->validate($request, ['user_id' => 'required', 'transaction_id' => 'required', 'user_type' => 'required|in:customer', 'remember_token' => 'required',]);

            /** @var Payment $payment */
            $payment = Payment::where('transaction_id', $request->transaction_id)->valid()->first();

            if (!$payment) return api_response($request, null, 404); elseif ($payment->isFailed()) return api_response($request, null, 500, 'Payment failed');
            elseif ($payment->isPassed()) return api_response($request, null, 200);

            /** @var Customer $user */
            $user = $payment->payable->user;
            $sheba_credit = $user->shebaCredit();
            $paymentRepository->setPayment($payment);

            if ($sheba_credit < $payment->payable->amount) {
                $paymentRepository->changeStatus(['to' => 'validation_failed', 'from' => $payment->status, 'transaction_details' => $payment->transaction_details, 'log' => "Insufficient balance. Purchase Amount: $sheba_credit & Sheba Credit: $sheba_credit"]);
                $payment->status = 'validation_failed';
                $payment->update();
                return api_response($request, null, 400, ['message' => 'You don\'t have sufficient credit']);
            }

            try {
                DB::transaction(function () use ($payment, $user) {
                    $partner_order = PartnerOrder::find($payment->payable->type_id);
                    $remaining = (new ShebaBonusCredit())->setUser($user)->setSpentModel($partner_order)->deduct($payment->payable->amount);
                    if ($remaining > 0) {
                        $user->debitWallet($remaining);
                        $user->walletTransaction(['amount' => $remaining, 'type' => 'Debit', 'log' => 'Service Purchase.', 'partner_order_id' => $partner_order->id, 'created_at' => Carbon::now()]);
                    }
                });
                $paymentRepository->changeStatus(['to' => 'validated', 'from' => $payment->status, 'transaction_details' => $payment->transaction_details]);
                $payment->status = 'validated';
                $payment->update();
            } catch (QueryException $e) {
                app('sentry')->captureException($e);
                return api_response($request, null, 500);
            }

            return api_response($request, $user, 200);
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