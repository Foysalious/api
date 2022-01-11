<?php


namespace Sheba\MerchantEnrollment\PaymentMethod;

use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class PaymentMethodCompletion
{
    use ProtectedGetterTrait;

    protected $categories;
    protected $can_apply;
    protected $message = '';

    /**
     * @param $categories
     * @return $this
     */
    public function setCategories($categories): PaymentMethodCompletion
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param $can_apply
     * @return $this
     */
    public function setCanApply($can_apply): PaymentMethodCompletion
    {
        $this->can_apply = $can_apply;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message): PaymentMethodCompletion
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCanApply()
    {
        return $this->can_apply;
    }
}
