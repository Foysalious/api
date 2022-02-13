<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory\Category;

use App\Sheba\MerchantEnrollment\PersonalInformation;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;

class NIDSelfie extends MEFFormCategory
{
    public $category_code = 'nid_selfie';

    public function completion(): array
    {
        return [
            'en' => $this->percentageCalculation(),
            'bn' => $this->getBengaliPercentage()
        ];
    }

    public function get(): CategoryGetter
    {
        return (new CategoryGetter());
    }

    public function getFormFieldData(): array
    {
        return [];
    }

    public function post($data)
    {

    }

    public function getFormFields()
    {
    }

    public function percentageCalculation(): float
    {
        $is_nid_verified = (new PersonalInformation())->setPartner($this->partner)->isNidVerified();
        $this->percentage = $is_nid_verified ? 100 : 0;
        return $this->percentage;
    }
}