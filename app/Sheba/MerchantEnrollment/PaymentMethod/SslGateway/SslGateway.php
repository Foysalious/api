<?php

namespace Sheba\MerchantEnrollment\PaymentMethod\SslGateway;

use App\Sheba\MerchantEnrollment\PaymentMethod\Completion;
use Sheba\MerchantEnrollment\Exceptions\InvalidListInsertionException;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethodCompletion;
use Sheba\MerchantEnrollment\Statics\MEFGeneralStatics;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class SslGateway extends PaymentMethod
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

    public function requiredDocuments(): array
    {
        return [
            'required_documents' => MEFGeneralStatics::required_documents()['ssl'],
            'terms_and_condition' => PaymentLinkStatics::paymentTermsAndConditionWebview()
        ];
    }
}