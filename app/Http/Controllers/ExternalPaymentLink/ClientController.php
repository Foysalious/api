<?php

namespace App\Http\Controllers\ExternalPaymentLink;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\PaymentClientAuthentication\Contract as PaymentClientAuthenticationRepo;
use Sheba\Dal\PaymentClientAuthentication\Status;
use Sheba\ExternalPaymentLink\Client;

class ClientController extends Controller
{
    /**
     * @var PaymentClientAuthenticationRepo
     */
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

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                "name"            => "required|max:120",
                "status"          => "required|in:published,unpublished"
            ]);
            (new Client())->setRepository($this->paymentClientRepo)->setName($request->name)->setDetails($request->details)
                ->setWhitelistedIp($request->whitelisted_ips)->setClientId()->setClientSecret()
                ->setPartnerId($request->partner->id)->setStatus($request->status)->store();
            return api_response($request, '', 200, ["data" => ["message" => "Client created successfully"]]);
        } catch (ValidationException $exception) {
            $message = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
