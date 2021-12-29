<?php

namespace App\Http\Controllers\ResellerPayment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\ResellerPayment\Exceptions\StoreValidationException;
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

    public function store(Request $request): JsonResponse
    {
        try {
            $this->validate($request, ["key" => "required"]);
            $this->storeConfiguration->setPartner($request->partner)->setKey($request->key)
                ->setGatewayId($request->gateway_id)->setRequestData($request->configuration_data)->storeConfiguration();
            return api_response($request, null, 200);
        } catch (StoreValidationException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (InvalidConfigurationException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
