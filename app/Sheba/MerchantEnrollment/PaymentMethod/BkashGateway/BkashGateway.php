<?php

namespace Sheba\MerchantEnrollment\PaymentMethod\BkashGateway;

use App\Sheba\MerchantEnrollment\PaymentMethod\Completion;
use Sheba\MerchantEnrollment\Exceptions\InvalidListInsertionException;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethodCompletion;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class BkashGateway extends PaymentMethod
{
    public function categoryDetails(MEFFormCategory $category): CategoryGetter
    {
        return $category->get();
    }

    public function getStaticCategoryFormData(MEFFormCategory $category)
    {
        return $category->getFormFields();
    }

    /**
     * @throws InvalidListInsertionException
     * @throws InvalidKeyException
     */
    public function completion(): PaymentMethodCompletion
    {
        return (new Completion())->setPartner($this->partner)->setPaymentGateway($this->payment_method)->get();
    }

    public function requiredDocuments()
    {
        // TODO: Implement requiredDocuments() method.
    }

    public function apply()
    {
        // TODO: Implement apply() method.
    }
}