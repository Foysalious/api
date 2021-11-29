<?php


namespace App\Http\Controllers\ExternalPaymentLink;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\PaymentClientAuthentication\Model as PaymentClientAuthentication;
use Sheba\ExternalPaymentLink\Exceptions\ExternalPaymentLinkException;
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
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param ExternalPayments $payments
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request, ExternalPayments $payments)
    {
        try {
            $this->validate($request, ["transaction_id" => "required"]);
            /** @var PaymentClientAuthentication $client */
            $client  = $request->client;
            $payment = $payments->setClient($client)->getPaymentDetails($request->transaction_id);
            return api_response($request, $payment, 200, ['data' => $payment]);

        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (ExternalPaymentLinkException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $exception) {
            logError($exception);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param ExternalPayments $payments
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus(Request $request, ExternalPayments $payments)
    {
        try {
            /** @var PaymentClientAuthentication $client */
            $client = $request->client;
            $status = $payments->setClient($client)->getGatewayStatus();
            return api_response($request, $payments, 200, ['data' => $status]);

        } catch (ExternalPaymentLinkException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
