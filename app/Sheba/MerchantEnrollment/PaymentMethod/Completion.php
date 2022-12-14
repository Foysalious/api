<?php

namespace App\Sheba\MerchantEnrollment\PaymentMethod;

use Sheba\MerchantEnrollment\Exceptions\InvalidListInsertionException;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\MEFFormCategoryFactory;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethodCompletion;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class Completion
{
    private $partner;

    private $payment_gateway;

    private $overall_completion;

    private $message;

    /**
     * @param mixed $payment_gateway
     * @return Completion
     */
    public function setPaymentGateway($payment_gateway): Completion
    {
        $this->payment_gateway = $payment_gateway;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return Completion
     */
    public function setPartner($partner): Completion
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $message
     * @return Completion
     */
    public function setMessage($message): Completion
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return PaymentMethodCompletion
     * @throws InvalidKeyException
     * @throws InvalidListInsertionException
     */
    public function get(): PaymentMethodCompletion
    {
        $completion = $this->completionPercentage();
        $this->calculateAndSetOverallCompletion($completion);
        return (new PaymentMethodCompletion())->setCanApply($this->overall_completion == 100 ? 1 : 0)
            ->setOverallCompletion($this->overall_completion)->setCategories($completion)->setMessage($this->message);
    }

    /**
     * @return array
     * @throws InvalidListInsertionException
     * @throws InvalidKeyException
     */
    public function completionPercentage(): array
    {
        $list = (new MEFFormCategoryFactory())->setPaymentGateway($this->payment_gateway)->setPartner($this->partner)->getAllCategory();
        $iterator   = $list->getIterator();
        $completion = [];
        while ($iterator->valid()) {
            /** @var MEFFormCategory $current */
            $current      = $iterator->current();
            $completion[] = $current->getCompletionDetails()->toArray();
            $iterator->next();
        }
        return $completion;
    }

    public function calculateAndSetOverallCompletion($completion)
    {
        $overall_completion = 0;
        foreach ($completion as $c)
            $overall_completion += ($c["completion_percentage"]["en"]);

        $this->overall_completion = round($overall_completion / count($completion),2);
    }
}