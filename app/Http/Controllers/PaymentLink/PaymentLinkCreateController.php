<?php

namespace App\Http\Controllers\PaymentLink;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Payment\Methods\Bkash\Bkash;
use Sheba\Payment\Methods\Nagad\Nagad;
use Sheba\Payment\Presenter\PaymentMethodDetails;
use Sheba\PaymentLink\Exceptions\InvalidGatewayChargesException;
use Sheba\PaymentLink\PaymentLinkStatics;

class PaymentLinkCreateController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customLinkCreateData(Request $request): JsonResponse
    {
        try {
            $data = PaymentLinkStatics::customPaymentLinkData();
            return api_response($request, $data, 200, ["data" => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function subscriptionWiseCharges(Request $request): JsonResponse
    {
        try {
            $data = array();
            $others = array();
            $partner = $request->partner;
            if (isset($partner->subscription->validPaymentGateway))
                $gateway_charges = json_decode($partner->subscription->validPaymentGateway->gateway_charges,1);
            else throw new InvalidGatewayChargesException();

            foreach ($gateway_charges as $charge) {
                if($charge['key'] === Bkash::NAME || $charge['key'] === Nagad::NAME)
                    $data[] = array_merge($charge, (new PaymentMethodDetails($charge['key']))->toArray());
                else
                    $others[] = array_merge($charge, (new PaymentMethodDetails($charge['key']))->toArray());
            }
            $biggest = $others[0];
            foreach ($others as $charge) {
                if(($charge['gateway_charge'] + $charge['fixed_charge']) > ($biggest['gateway_charge'] + $biggest['fixed_charge']))
                    $biggest = $charge;
            }
            $biggest['key'] = 'online';
            $data[] = array_merge($biggest, (new PaymentMethodDetails($biggest['key']))->toArray());

            return api_response($request, $gateway_charges, 200, ["data" => $data]);
        } catch (InvalidGatewayChargesException $exception) {
            logError($exception);
            return api_response($request, null, $exception->getCode(), ["message" => $exception->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
