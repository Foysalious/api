<?php namespace Sheba\Payment\Presenter;

use Sheba\Dal\PgwStore\Contract as PgwStore;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\PresentableDTO;

class PaymentMethodDetails extends PresentableDTO
{
    private $name;
    private $isPublished = true;
    private $description = "";
    private $asset;
    private $methodName;
    private $nameBn;
    private $icon;
    private $cashInCharge;

    public function __construct($method_name, $payable_type=null)
    {
        if($payable_type == "payment_link")
            $this->setMethodDetailsForPaymentLink($method_name);
        else
            $this->setMethodDetailsFromConfig($method_name);
    }

    /**
     * @param bool $is_published
     * @return $this
     */
    public function setIsPublished($is_published)
    {
        $this->isPublished = $is_published;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'name_bn' => $this->nameBn,
            'is_published' => (int)$this->isPublished,
            'description' => $this->description,
            'asset' => $this->asset,
            'method_name' => $this->methodName,
            'icon' => $this->icon,
            'cash_in_charge' => $this->cashInCharge,
        ];
    }

    /**
     * @param $key
     * @return mixed
     * @throws InvalidPaymentMethod
     */
    public function getPgwAccountDetails($key)
    {
        /** @var PgwStore $store */
        $store = app()->make(PgwStore::class);
        $store_details = $store->where('key', $key)->first();
        if(!$store_details) throw new InvalidPaymentMethod("Invalid payment method key");
        return $store_details;
    }

    private function setMethodDetailsForPaymentLink($method_name)
    {
        $pgw_store_details = $this->getPgwAccountDetails($method_name);
        $this->name = $pgw_store_details->name;
        $this->asset = $pgw_store_details->asset;
        $this->methodName = $pgw_store_details->method_name;
        $this->nameBn = $pgw_store_details->name_bn;
        $this->icon = $pgw_store_details->icon;
        $this->cashInCharge = 0;
    }

    private function setMethodDetailsFromConfig($method_name)
    {
        $details = (include dirname(__FILE__) . "/method_details.php")[$method_name];
        $this->name = $details['name'];
        $this->asset = $details['asset'];
        $this->methodName = $details['method_name'];
        $this->nameBn = $details['name_bn'];
        $this->icon = $details['icon'];
        $this->cashInCharge = $details['cash_in_charge'];
    }
}
