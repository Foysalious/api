<?php

namespace Sheba\MerchantEnrollment\PaymentMethod\SslGateway;

use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;

class SslGateway extends PaymentMethod
{

    public function categoryDetails(MEFFormCategory $category)
    {
        return $category->get();
    }
}