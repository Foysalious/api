<?php


namespace Sheba\NeoBanking\Statics;


class FormStatics
{
    public static function personal()
    {
        return config('neo_banking.category_form_items.personal');
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
}
