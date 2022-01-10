<?php

namespace Sheba\MerchantEnrollment\Statics;

class FormStatics
{
    public static function institution()
    {
        return config('reseller_payment.category_form_items.institution');
    }

    public static function documents()
    {
        return config('reseller_payment.category_form_items.documents');
    }
}