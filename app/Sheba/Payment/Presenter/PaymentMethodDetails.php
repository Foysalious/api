<?php namespace Sheba\Payment\Presenter;

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

    public function __construct($method_name)
    {
        $details = (include dirname(__FILE__) . "/method_details.php")[$method_name];
        $this->name = $details['name'];
        $this->asset = $details['asset'];
        $this->methodName = $details['method_name'];
        $this->nameBn = $details['name_bn'];
        $this->icon = $details['icon'];
        $this->cashInCharge = $details['cash_in_charge'];
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
}
