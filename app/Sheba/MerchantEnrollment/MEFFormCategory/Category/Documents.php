<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory\Category;

use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;

class Documents extends MEFFormCategory
{
    public $category_code = 'documents';

    public function completion(): array
    {
        return [
            'en' => 100,
            'bn' => 100
        ];
    }

    public function get(): CategoryGetter
    {
    }

    public function post($data)
    {

    }

    public function getFormFields()
    {
    }
}