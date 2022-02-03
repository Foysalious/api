<?php

namespace Sheba\ResellerPayment\Statics;

use Sheba\PaymentLink\PaymentLinkStatics;

class StoreConfigurationStatic
{
    public static function getStoreConfiguration($key)
    {
        return config("store_configuration.dynamic_store_configuration.$key");
    }

    public static function storeConfigurationGetResponse($configuration): array
    {
        return [
            "configuration"               => $configuration,
            "terms_and_condition_webview" => PaymentLinkStatics::paymentTermsAndConditionWebview()
        ];
    }

    public static function validateStoreConfigurationPost(): array
    {
        return [
            "key"                => "required",
            "configuration_data" => "required",
            "gateway_id"         => "required"
        ];
    }

    public static function statusUpdateValidation(): array
    {
        return [
            "key" => "required",
            "gateway_id" => "required|numeric",
            "status" => "required|boolean"
        ];
    }
}