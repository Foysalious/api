<?php


namespace Sheba\Loan;


class CompletionStatics
{
    static function businessV2(){
        return [
            'fixed_asset',
            'security_check',
            'business_category',
            'sector',
            'industry_and_business_nature',
        ];
    }
    static function business(){
        return [
            'fixed_asset',
            'security_check',
            'business_category',
            'sector',
            'industry_and_business_nature',
            'trade_license',
            'trade_license_issue_date',
            'registration_no',
            'registration_year',
            'country',
            'street',
            'thana',
            'zilla',
            'post_code',
        ];
    }

}
