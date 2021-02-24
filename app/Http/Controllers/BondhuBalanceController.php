<?php namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\Payment;
use App\Repositories\PaymentStatusChangeLogRepository;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class BondhuBalanceController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        try {
            /** @var Payment $payment */
            $payment = Payment::where('transaction_id', $request->transaction_id)->valid()->first();
            if (!$payment)
                return api_response($request, null, 404);
            elseif ($payment->isComplete())
                return api_response($request, 1, 200, ['message' => 'Payment completed']);
            elseif (!$payment->canComplete())
                return api_response($request, null, 400, ['message' => 'Payment validation failed.']);
            $payment = $payment_manager->setMethodName(PaymentStrategy::BONDHU_BALANCE)->setPayment($payment)->complete();
            if ($payment->isComplete())
                $message = 'Payment successfully completed'; elseif ($payment->isPassed())
                $message = 'Your payment has been received but there was a system error. It will take some time to transaction your order. Call 16516 for support.';
            return api_response($request, null, 200, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param PaymentStatusChangeLogRepository $paymentRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request, PaymentStatusChangeLogRepository $paymentRepository)
    {
        try {
            $this->validate($request, ['transaction_id' => 'required']);
            /** @var Payment $payment */
            $payment = Payment::where('transaction_id', $request->transaction_id)->valid()->first();
            if (!$payment)
                return api_response($request, null, 404);
            elseif ($payment->isFailed())
                return api_response($request, null, 500, ['message' => 'Payment failed']);
            elseif ($payment->isPassed())
                return api_response($request, null, 200);
            /** @var Partner $user */
            $user           = $payment->payable->user;
            $partner_credit = $user->wallet;
            $paymentRepository->setPayment($payment);
            if ($partner_credit < $payment->payable->amount) {
                $paymentRepository->create([
                    'to'                  => 'validation_failed',
                    'from'                => $payment->status,
                    'transaction_details' => $payment->transaction_details,
                    'log'                 => "Insufficient balance. Purchase Amount: " . $payment->payable->amount . " & Partner Credit: $partner_credit"
                ]);
                $payment->status = 'validation_failed';
                $payment->update();
                return api_response($request, null, 400, ['message' => 'You don\'t have sufficient credit']);
            }
            try {
                $transaction = '';
                DB::transaction(function () use ($payment, $user, $partner_credit, &$transaction) {
                    $partner_order = PartnerOrder::find($payment->payable->type_id);
                    if ($payment->payable->amount > 0) {
                        $transaction = (new WalletTransactionHandler())->setModel($user)->setLog("Service Purchase (ORDER ID: {$partner_order->code()})")->setSource(TransactionSources::SERVICE_PURCHASE)->setType(Types::debit())->setAmount($payment->payable->amount)->store();
                    }
                });
                $paymentRepository->create([
                    'to'                  => 'validated',
                    'from'                => $payment->status,
                    'transaction_details' => $payment->transaction_details
                ]);
                $payment->status              = 'validated';
                $payment->transaction_details = json_encode(array(
                    'payment_id'     => $payment->id,
                    'transaction_id' => $transaction ? $transaction->id : null,
                ));
                $payment->update();
            } catch (QueryException $e) {
                logError($e);
                return api_response($request, null, 500);
            }
            return api_response($request, $user, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
