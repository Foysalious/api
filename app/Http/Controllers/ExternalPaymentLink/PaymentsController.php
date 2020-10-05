<?php


namespace App\Http\Controllers\ExternalPaymentLink;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\Dal\PaymentClientAuthentication\Model as PaymentClientAuthentication;
use Sheba\ExternalPaymentLink\Exceptions\ExternalPaymentLinkException;
use Sheba\ExternalPaymentLink\Exceptions\InvalidEmiMonthException;
use Sheba\ExternalPaymentLink\Exceptions\InvalidTransactionIDException;
use Sheba\ExternalPaymentLink\ExternalPayments;
use Sheba\ExternalPaymentLink\Statics\ExternalPaymentStatics;

class PaymentsController extends Controller
{
    public function initiate(Request $request, ExternalPayments $payments)
    {
        try {
            $this->validate($request, ExternalPaymentStatics::getPaymentInitiateValidator());
            /** @var PaymentClientAuthentication $client */
            $client  = $request->client;
            $payment = $payments->setClient($client)->setData($request)->beforeCreateValidate()->create();
            return api_response($request, $payment, 200, ['data' => $payment]);

        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (ExternalPaymentLinkException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            dd($e->getMessage());
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
