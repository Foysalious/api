<?php

namespace Sheba\NeoBanking\Statics;

class FormStatics
{
    public static function personal()
    {
        return config('neo_banking.category_form_items.personal');
    }

    public static function dynamic_banner()
    {
        return config('neo_banking.category_form_items.dynamic_banner');
    }

    public static function institution()
    {
        return config('neo_banking.category_form_items.institution');
    }

    public static function nominee()
    {
        return config('neo_banking.category_form_items.nominee');
    }

    public static function account()
    {
        return config('neo_banking.category_form_items.account');
    }

    public static function documents()
    {
        return config('neo_banking.category_form_items.documents');
    }

    public static function nidSelfie()
    {
        return config('neo_banking.category_form_items.nid_selfie');
    }
}
