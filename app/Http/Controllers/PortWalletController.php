<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\ShebaPayment;

class PortWalletController extends Controller
{
    /** @var ShebaPayment */
    private $shebaPayment;

    /**
     * PortWalletController constructor.
     * @param ShebaPayment $sheba_payment
     * @throws \ReflectionException
     */
    public function __construct(ShebaPayment $sheba_payment)
    {
        $this->shebaPayment = $sheba_payment;
        $this->shebaPayment->setMethod('port_wallet');
    }

    public function ipn(Request $request)
    {
        $payment = $this->getPaymentByRequest($request);

        if (!$payment) return api_response($request, null, 400, ['message' => "Invalid Payment"]);

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

        dd($payment);

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
            $payment = $this->shebaPayment->complete($payment);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
        }
        return $payment;
    }
}
