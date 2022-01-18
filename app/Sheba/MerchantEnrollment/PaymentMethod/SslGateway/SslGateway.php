<?php

namespace Sheba\MerchantEnrollment\PaymentMethod\SslGateway;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\MerchantEnrollment\PaymentMethod\ApplicationSubmit;
use App\Sheba\MerchantEnrollment\PaymentMethod\Completion;
use App\Sheba\ResellerPayment\Exceptions\MORServiceServerError;
use Sheba\MerchantEnrollment\Exceptions\IncompleteSubmitData;
use Sheba\MerchantEnrollment\Exceptions\InvalidListInsertionException;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethodCompletion;
use Sheba\MerchantEnrollment\Statics\MEFGeneralStatics;
use Sheba\MerchantEnrollment\Statics\PaymentMethodStatics;
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
        $message = PaymentMethodStatics::completionPageMessage();
        return (new Completion())->setPartner($this->partner)->setPaymentGateway($this->payment_method)
            ->setMessage($message)->get();
    }

    public function requiredDocuments(): array
    {
        return [
            'required_documents' => MEFGeneralStatics::required_documents()['ssl'],
            'terms_and_condition' => PaymentLinkStatics::paymentTermsAndConditionWebview()
        ];
    }

    /**
     * @throws IncompleteSubmitData
     * @throws InvalidKeyException
     * @throws InvalidListInsertionException
     * @throws MORServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    public function applicationApply(): array
    {
        (new ApplicationSubmit())->setPartner($this->partner)->setPaymentGateway($this->payment_method)->submit();
        return PaymentMethodStatics::APPLY_SUCCESS_MESSAGE;
    }

    /**
     * @param $paymentGatewayKey
     * @return mixed
     * @throws InvalidKeyException
     */
    public function documentServices($paymentGatewayKey)
    {
        return $this->documentServiceList($paymentGatewayKey);
    }
}