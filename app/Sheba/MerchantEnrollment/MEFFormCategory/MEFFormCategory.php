<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

use Sheba\MerchantEnrollment\PartnerAllInformation;

abstract class MEFFormCategory
{
    protected $category_code;
    protected $exclude_form_keys = array();
    protected $percentage;
    protected $partner;
    protected $payment_gateway;
    /** @var PartnerAllInformation */
    protected $partnerAllInfo;

    public function __construct()
    {
        $this->partnerAllInfo = (new PartnerAllInformation());
    }

    /**
     * @param mixed $partner
     * @return MEFFormCategory
     */
    public function setPartner($partner): MEFFormCategory
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $payment_gateway
     * @return MEFFormCategory
     */
    public function setPaymentGateway($payment_gateway): MEFFormCategory
    {
        $this->payment_gateway = $payment_gateway;
        return $this;
    }

    /**
     * @param array $exclude_form_keys
     * @return MEFFormCategory
     */
    public function setExcludeFormKeys(array $exclude_form_keys): MEFFormCategory
    {
        $this->exclude_form_keys = $exclude_form_keys;
        return $this;
    }

    abstract public function completion();

    abstract public function get();

    abstract public function post($data);

    protected function getBengaliPercentage(): string
    {
        return convertNumbersToBangla($this->percentage, false);
    }

    protected function getFormData($formItems)
    {
        $data      = [];
        $formData  = $this->partnerAllInfo->setPartner($this->partner)->getByCode($this->category_code);
        dd($formItems);
    }

}