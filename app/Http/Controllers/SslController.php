<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Sheba\Payment\PaymentManager;
use Sheba\TopUp\Gateway\Ssl;

class SslController extends Controller
{
    /** @var Ssl */
    private $ssl;

    public function __construct(Ssl $ssl)
    {
        $this->ssl = $ssl;
    }

    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        $redirect_url = config('sheba.front_url');
        try {
            /** @var Payment $payment */
            $payment = Payment::where('gateway_transaction_id', $request->tran_id)->first();
            if ($payment) {
                $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;
                $method = $payment->paymentDetails->last()->method;
                if ($payment->isValid() && !$payment->isComplete()) {
                    $payment_manager->setMethodName($method)->setPayment($payment)->complete();
                }
            } else {
                throw new Exception('Payment not found to validate.');
            }
        } catch (Throwable $e) {
            logError($e);
        }
        return redirect($redirect_url);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function validateTopUp(Request $request)
    {
        $this->validate($request, [
            'vr_guid' => 'required',
            'guid'    => 'required',
        ]);
        $response = $this->ssl->getRecharge($request->guid, $request->vr_guid);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function checkBalance(Request $request)
    {
        $response = $this->ssl->getBalance();
        return api_response($request, $response, 200, ['data' => $response]);
    }
}
