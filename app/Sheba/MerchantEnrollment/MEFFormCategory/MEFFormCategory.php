<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

abstract class MEFFormCategory
{
    protected $code;
    protected $exclude_form_keys = array();
    protected $percentage;
    protected $partner;
    protected $payment_gateway;

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
//        $formData  = $this->bankAccountData->getByCode($this->code);
        dd($formItems);
    }

}