<?php

namespace App\Http\Controllers\ResellerPayment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Sheba\ResellerPayment\Exceptions\StoreValidationException;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;
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
        } catch (ResellerPaymentException $e) {
            logError($e);
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $this->validate($request, StoreConfigurationStatic::validateStoreConfigurationPost());
            $this->storeConfiguration->setPartner($request->partner)->setKey($request->key)
                ->setGatewayId($request->gateway_id)->setRequestData($request->configuration_data)->storeConfiguration();
            return api_response($request, null, 200);
        } catch (StoreValidationException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ResellerPaymentException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
