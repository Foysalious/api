<?php

namespace App\Http\Controllers\ExternalPaymentLink;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\PaymentClientAuthentication\Contract as PaymentClientAuthenticationRepo;
use Sheba\ExternalPaymentLink\Client;

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
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param Client $clients
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Client $clients)
    {
        try {
            $this->validate($request, $this->validationRules());
            $clients->setRepository($this->paymentClientRepo)->setName($request->name)->setDetails($request->details)
                ->setWhitelistedIp($request->whitelisted_ips)->setClientId()->setClientSecret()->setDefaultFields($request)
                ->setPartnerId($request->partner->id)->setStatus($request->status)->store($request->manager_resource);
            return api_response($request, '', 200, ["data" => ["message" => "Client created successfully"]]);
        } catch (ValidationException $exception) {
            $message = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function validationRules()
    {
        return [
            "name"            => "required|max:120",
            "status"          => "required|in:published,unpublished",
            "whitelisted_ips" => "required",
            "details"         => "required"
        ];
    }

    /**
     * @param $partner
     * @param $client_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clientSecretGenerate($partner, $client_id, Request $request)
    {
        try {
            $client = (new Client())->setRepository($this->paymentClientRepo)->setId($client_id)
                ->setClientSecret()->updateSecret($request->manager_resource);
            return api_response($request, $client, 200, ['data' => [
                "client" => $client,
                "message"=> "client secret updated"
            ]]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $client_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($partner, $client_id, Request $request)
    {
        try {
            $client = (new Client())->setRepository($this->paymentClientRepo)->setId($client_id)->client();
            return api_response($request, $client, 200, ['data' => ["client" => $client]]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $client_id
     * @param Request $request
     * @param Client $clients
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($partner, $client_id, Request $request, Client $clients)
    {
        try {
            $this->validate($request, $this->validationRules());
            $client = $clients->setRepository($this->paymentClientRepo)->setId($client_id)->setDefaultFields($request)
                ->setName($request->name)->setDetails($request->details)->setWhitelistedIp($request->whitelisted_ips)
                ->setStatus($request->status)->update($request->manager_resource);
            return api_response($request, $client, 200, ['data' => [
                "message" => "client information updated",
                "client"  => $client
            ]]);
        } catch (ValidationException $exception) {
            $message = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
