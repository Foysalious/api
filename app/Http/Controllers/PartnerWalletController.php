<?php namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\Payment;

use App\Repositories\PaymentRepository;
use Carbon\Carbon;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\Payment\ShebaPayment;
use DB;

class PartnerWalletController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @param PaymentRepository $paymentRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request, PaymentRepository $paymentRepository)
    {
        try {
            $this->validate($request, ['transaction_id' => 'required']);

            /** @var Payment $payment */
            $payment = Payment::where('transaction_id', $request->transaction_id)->valid()->first();

            if (!$payment) return api_response($request, null, 404);
            elseif ($payment->isFailed()) return api_response($request, null, 500, 'Payment failed');
            elseif ($payment->isPassed()) return api_response($request, null, 200);

            /** @var Partner $user */
            $user = $payment->payable->user;

            $partner_credit = $user->wallet;
            $paymentRepository->setPayment($payment);

            if ($partner_credit < $payment->payable->amount) {
                $paymentRepository->changeStatus([
                    'to' => 'validation_failed',
                    'from' => $payment->status,
                    'transaction_details' => $payment->transaction_details,
                    'log' => "Insufficient balance. Purchase Amount: " . $payment->payable->amount . " & Partner Credit: $partner_credit"
                ]);
                $payment->status = 'validation_failed';
                $payment->update();
                return api_response($request, null, 400, ['message' => 'You don\'t have sufficient credit']);
            }

            try {
                DB::transaction(function () use ($payment, $user, $partner_credit) {
                    $partner_order = PartnerOrder::find($payment->payable->type_id);
                    $user->debitWallet($payment->payable->amount);
                    $user->walletTransaction([
                        'amount' => $payment->payable->amount,
                        'type' => 'Debit',
                        'log' => 'Service Purchase.',
                        'partner_order_id' => $partner_order->id,
                        'created_at' => Carbon::now()
                    ]);
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