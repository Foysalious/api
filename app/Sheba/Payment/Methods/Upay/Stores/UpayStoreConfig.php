<?php

namespace Sheba\Payment\Methods\Upay\Stores;

use Illuminate\Contracts\Support\Arrayable;

class UpayStoreConfig implements Arrayable
{
    public $merchant_name;
    public $merchant_id;
    public $merchant_key;
    public $merchant_mobile;
    public $merchant_country_code;
    public $merchant_city;
    public $merchant_category_code;
    public $transaction_currency_code;
    public $redirect_url;
    public function build(array $data){
        foreach ($data as $key=>$value){
            if(property_exists($this,$key)){
                $this->$key=$value;
            }
        }
        return $this;
    }
    public function getFromConfig($store_name='default'){
        return $this->build(config("payment.upay.stores.$store_name"));
    }
    public function toArray()
    {
        return call_user_func('get_object_vars', $this);
    }
}