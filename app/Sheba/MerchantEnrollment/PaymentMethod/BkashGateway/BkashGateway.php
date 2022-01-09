<?php

namespace Sheba\MerchantEnrollment\PaymentMethod\BkashGateway;

use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;

class BkashGateway extends PaymentMethod
{
    public function categoryDetails(MEFFormCategory $category): CategoryGetter
    {
        return $category->get();
    }
}