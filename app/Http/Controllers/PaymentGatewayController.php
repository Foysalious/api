<?php

namespace App\Http\Controllers;

use Sheba\Dal\PaymentGateway\Contract as PaymentGatewayRepository;

class PaymentGatewayController extends Controller
{
    private $paymentGateway;

    /**
     * PaymentGatewayController constructor.
     * @param PaymentGatewayRepository $paymentGateway
     */
    public function __construct(PaymentGatewayRepository $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * @param $service_type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentGateways($service_type)
    {
        if ($service_type == "affiliate"){
            $service = "App\\Models\\Affiliate";
        } elseif ($service_type == "customer"){
            $service = "App\\Models\\Customer";
        } elseif ($service_type == "partner"){
            $service = "App\\Models\\Partner";
        } elseif($service_type == "business"){
            $service = "App\\Models\\Business";
        }

        $payment_gateways = $this->paymentGateway->builder()->where('service_type', $service)->orderBy('order', 'asc')->get();
        return api_response($service_type, $payment_gateways, 200, ['payment_gateways' => $payment_gateways]);
    }

}
