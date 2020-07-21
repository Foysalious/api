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
        $micro  = array_merge($term, ['part_time_employee', 'product_price', 'employee_salary', 'office_rent', 'utility_bill', 'marketing_cost', 'other_cost', 'registration_no', 'avg_sell', 'min_sell', 'max_sell', 'yearly_income', 'establishment_year', 'addresss']);
        $oldApp = array_merge($term, [
            'trade_license_issue_date',
            'registration_no',
            'registration_year',
            'country',
            'street',
            'thana',
            'zilla',
            'post_code',
            'address',
            'yearly_sales',
            'proof_of_photograph',
            'licence_agreement_checked',
            'ipdc_data_agreement_checked',
            'ipdc_cib_agreement_checked'
        ]);
        return $version == 2 ? $type == 'micro' ? $micro : $term : $oldApp;
    }
}
