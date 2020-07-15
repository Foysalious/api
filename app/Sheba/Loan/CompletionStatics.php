<?php


namespace Sheba\Loan;


class CompletionStatics
{
    static function business($version = null, $type = null)
    {

        $term   = [
            'fixed_asset',
            'security_check',
            'business_category',
            'sector',
            'industry_and_business_nature',
            'trade_license'
        ];
        $micro  = array_merge($term  ,['full_time_employee','product_price','office_rent','utility_bill','marketing_cost','other_cost','registration_no','avg_sell','min_sell','max_sell','location','yearly_income','business_category','establishment_year']);
        $oldApp = array_merge($micro , [
            'trade_license_issue_date',
            'registration_no',
            'registration_year',
            'country',
            'street',
            'thana',
            'zilla',
            'post_code',
            'address',
            'yearly_sales'
        ]);
        return $version == 2 ? $type == 'micro' ? $micro : $term : $oldApp;
    }
}
