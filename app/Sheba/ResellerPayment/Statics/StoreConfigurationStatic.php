<?php

namespace Sheba\ResellerPayment\Statics;

class StoreConfigurationStatic
{
    public static function getStoreConfiguration($key)
    {
        return config("store_configuration.dynamic_store_configuration.$key");
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