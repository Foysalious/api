<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

use Sheba\MerchantEnrollment\MEFForm\FormItemBuilder;
use Sheba\MerchantEnrollment\PartnerAllInformation;
use Sheba\MerchantEnrollment\Statics\PaymentMethodStatics;

abstract class MEFFormCategory
{
    protected $title;
    protected $category_code;
    protected $exclude_form_keys = array();
    protected $percentage;
    protected $partner;
    protected $payment_gateway;
    /** @var PartnerAllInformation */
    protected $partnerAllInfo;
    protected $data;

    public function __construct()
    {
        $this->setTitle(PaymentMethodStatics::categoryTitles($this->category_code));
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

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): MEFFormCategory
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }


    abstract public function completion();

    abstract public function get(): CategoryGetter;

    abstract public function getFormFields();

    abstract public function post($data);

    protected function getBengaliPercentage(): string
    {
        return convertNumbersToBangla($this->percentage, false);
    }

    protected function getFormData($formItems, $formData): CategoryGetter
    {
        $data      = [];
        $formItemBuilder = (new FormItemBuilder())->setData($formData);
        foreach ($formItems as $item)
            $data[] = $formItemBuilder->build($item);

        $this->setData($data);
        return (new CategoryGetter())->setCategory($this);
    }

}