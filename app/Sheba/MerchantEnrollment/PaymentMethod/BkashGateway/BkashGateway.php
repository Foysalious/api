<?php

namespace Sheba\MerchantEnrollment\PaymentMethod\BkashGateway;

use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;

class BkashGateway extends PaymentMethod
{
    public function categoryDetails(MEFFormCategory $category)
    {
        return $category->get();
    }
}