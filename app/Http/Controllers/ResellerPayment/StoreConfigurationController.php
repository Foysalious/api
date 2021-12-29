<?php

namespace App\Http\Controllers\ResellerPayment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\ResellerPayment\StoreConfiguration;

class StoreConfigurationController extends Controller
{
    private $storeConfiguration;

    public function __construct(StoreConfiguration $configuration)
    {
        $this->storeConfiguration = $configuration;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        try {
            $this->validate($request, ["key" => "required"]);
            $configuration = $this->storeConfiguration->setPartner($request->partner)->setKey($request->key)->getConfiguration();
            return api_response($request, $configuration, 200, ['data' => $configuration]);
        } catch (\Throwable $e) {
            dd($e);
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, ["key" => "required"]);
            $configuration = $this->storeConfiguration->setPartner($request->partner)->setKey($request->key)
                ->setGatewayId($request->gateway_id)->setRequestData($request->configuration_data)->storeConfiguration();
            return api_response($request, $configuration, 200, ['data' => $configuration]);
        } catch (\Throwable $e) {
            dd($e);
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
