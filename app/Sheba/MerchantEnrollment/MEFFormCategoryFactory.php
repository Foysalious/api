<?php

namespace Sheba\MerchantEnrollment;

use App\Models\Partner;
use Sheba\MerchantEnrollment\Exceptions\InvalidMEFFormCategoryCodeException;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\Statics\PaymentMethodStatics;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class MEFFormCategoryFactory
{
    /** @var Partner $partner */
    private $partner;
    private $classPath;
    private $payment_gateway;

    public function __construct()
    {
        $this->classPath = "Sheba\\MerchantEnrollment\\MEFFormCategory\\";
    }

    /**
     * @param mixed $payment_gateway
     * @return MEFFormCategoryFactory
     */
    public function setPaymentGateway($payment_gateway): MEFFormCategoryFactory
    {
        $this->payment_gateway = $payment_gateway;
        return $this;
    }


    /**
     * @param Partner $partner
     * @return MEFFormCategoryFactory
     */
    public function setPartner(Partner $partner): MEFFormCategoryFactory
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $code
     * @return MEFFormCategory
     * @throws InvalidKeyException|InvalidMEFFormCategoryCodeException
     */
    public function getCategoryByCode($code): MEFFormCategory
    {
        $categoryList = PaymentMethodStatics::paymentGatewayCategoryList($this->payment_gateway->key);
        if (isset($categoryList[$code])) {
            $exclude_keys_list = PaymentMethodStatics::paymentMethodWiseExcludedKeys($this->payment_gateway->key);
            $exclude_keys = $exclude_keys_list[$code];
            $category = $categoryList[$code];
            /** @var MEFFormCategory $cls */
            $cls = app("$this->classPath$category");
            $cls->setPartner($this->partner)->setExcludeFormKeys($exclude_keys)->setPaymentGateway($this->payment_gateway);
            return $cls;
        }
        throw new InvalidMEFFormCategoryCodeException();
    }
}