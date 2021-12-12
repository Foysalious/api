<?php

namespace Sheba\AccountingEntry\Statics;

class AccountingStatics
{
    public static function getFaqUrl(): string
    {
        return config('sheba.partners_base_url') . "/" . "hishab-khata";
    }

    public static function getAccountingTrainingVideoKey(): string
    {
        return "accounting_dashboard";
    }

    public static function getFaqAndTrainingVideoKey(): array
    {
        return [
            'video_key' => self::getAccountingTrainingVideoKey(),
            'faq_url' => self::getFaqUrl()
        ];
    }
}