<?php

namespace App\Http\Controllers\QRPayment;

use App\Http\Controllers\Controller;
use App\Sheba\QRPayment\GatewayAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    /**
     * @param Request $request
     * @param GatewayAccounts $accounts
     * @return JsonResponse
     */
    public function index(Request $request, GatewayAccounts $accounts): JsonResponse
    {
        $partner = $request->partner;
        $gateway = $accounts->setPartner($partner)->getGateways();
        return http_response($request, null, 200, ["gateway_list" => $gateway]);
    }
}