<?php

namespace App\Http\Controllers\ExternalPaymentLink;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Dal\PaymentClientAuthentication\Contract as PaymentClientAuthenticationRepo;

class ClientController extends Controller
{
    private $paymentClientRepo;

    public function __construct(PaymentClientAuthenticationRepo $contract)
    {
        $this->paymentClientRepo = $contract;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $clients = $this->paymentClientRepo->getByPartner($request->partner->id)->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
            return api_response($request, $clients, 200, ['data' => $clients]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
