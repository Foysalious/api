<?php

namespace Sheba\MerchantEnrollment\PaymentMethod;

use App\Models\Partner;
use Carbon\Carbon;
use Sheba\MerchantEnrollment\Exceptions\InvalidCategoryPostDataException;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;

abstract class PaymentMethod
{
    /*** @var Partner */
    protected $partner;
    protected $payment_method;

    /**
     * @param mixed $partner
     * @return PaymentMethod
     */
    public function setPartner($partner): PaymentMethod
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $payment_method
     * @return PaymentMethod
     */
    public function setPaymentMethod($payment_method): PaymentMethod
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    abstract public function categoryDetails(MEFFormCategory $category): CategoryGetter;

    abstract public function getStaticCategoryFormData(MEFFormCategory $category);

    abstract public function completion(): PaymentMethodCompletion;


    public function postCategoryDetail(MEFFormCategory $category, $data)
    {
        return $category->post($data);
    }

    /**
     * @param MEFFormCategory $category
     * @param $post_data
     * @return $this
     * @throws InvalidCategoryPostDataException
     */
    public function validateCategoryDetail(MEFFormCategory $category, $post_data): PaymentMethod
    {
        $formData = $this->getStaticCategoryFormData($category);
        $data = json_decode($post_data,1);
        foreach ($formData as $form) {
            if ($form['mandatory']) {
                if (!isset($data[$form['id']])) throw new InvalidCategoryPostDataException($form['error']);
            }
            if ($form['input_type'] == 'date_picker') {
                try {
                    if(isset($data[$form['id']])) Carbon::parse($data[$form['id']]);
                } catch (\Throwable $e) {
                    throw new InvalidCategoryPostDataException($form['id']." date is Invalid");
                }
            }
        }

        return $this;
    }
}