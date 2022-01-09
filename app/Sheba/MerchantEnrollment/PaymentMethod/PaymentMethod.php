<?php

namespace Sheba\MerchantEnrollment\PaymentMethod;

use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;

abstract class PaymentMethod
{
    private $partner;

    /**
     * @param mixed $partner
     * @return PaymentMethod
     */
    public function setPartner($partner): PaymentMethod
    {
        $this->partner = $partner;
        return $this;
    }

    abstract public function categoryDetails(MEFFormCategory $category): CategoryGetter;
}