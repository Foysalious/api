<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Logs\ErrorLog;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;

class PortWalletController extends Controller
{
    /** @var PaymentManager */
    private $paymentManager;

    /**
     * PortWalletController constructor.
     * @param PaymentManager $payment_manager
     */
    public function __construct(PaymentManager $payment_manager)
    {
        $this->paymentManager = $payment_manager;
    }

    public function ipn(Request $request)
    {
        $payment = $this->getPaymentByRequest($request);

        if (!$payment) return api_response($request, null, 400, ['message' => "Invalid Payment"]);
        if (!$payment->isValid()||$payment->isComplete()){
            return api_response($request, null, 402,['message'=>"Invalid or completed payment"]);
        }
        $this->complete($payment);

        if (!$payment) return api_response($request, null, 500, ['message' => "Something bad happened."]);

        return api_response($request, null, 200, ['message' => "Gracious."]);
    }

    public function redirectWithoutValidation(Request $request)
    {
        $payment = $this->getPaymentByRequest($request);

        if (!$payment) return redirect(config('sheba.front_url'));

        return redirect($payment->payable->success_url . '?invoice_id=' . $payment->transaction_id);
    }

    public function validateOnRedirect(Request $request)
    {
        $payment = $this->getPaymentByRequest($request);

        if (!$payment) return redirect(config('sheba.front_url'));

        $this->complete($payment);

        if (!$payment) return redirect(config('sheba.front_url'));

        return redirect($payment->payable->success_url . '?invoice_id=' . $payment->transaction_id);
    }

    private function getPaymentByRequest(Request $request)
    {
        $this->validate($request, [
            'invoice' => 'required|string',
            'amount' => 'required|numeric',
            'status' => 'required|string',
        ]);

        return Payment::where('gateway_transaction_id', $request->invoice)->valid()->first();
    }

    private function complete(Payment $payment)
    {
        try {
            $payment = $this->paymentManager->setMethodName(PaymentStrategy::PORT_WALLET)->setPayment($payment)->complete();
        } catch (\Throwable $e) {
            logError($e);
        }
        return $payment;
    }
}
