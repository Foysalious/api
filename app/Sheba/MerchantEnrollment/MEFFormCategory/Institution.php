<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

use Sheba\MerchantEnrollment\Statics\FormStatics;

class Institution extends MEFFormCategory
{
    protected $category_code = 'institution';

    public function completion(): array
    {
        return [
            'en' => 100,
            'bn' => 100
        ];
    }

    public function get(): CategoryGetter
    {
        $formItems = FormStatics::institution();
        return $this->getFormData($formItems);
    }

    public function post($data)
    {
        // TODO: Implement post() method.
    }
}