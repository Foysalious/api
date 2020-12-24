<?php


namespace Sheba\Loan\Statics;


use Carbon\Carbon;
use Sheba\Dal\PartnerBankLoan\LoanTypes;

class BusinessStatics
{
    const INFO_KEY         = 'business_additional_information';
    const PHOTO_KEY        = 'proof_of_photograph';
    const SALES_INFO_KEY   = 'last_six_month_sales_information';
    const ONLINE_ORDER_KEY = 'online_order';

    public static function data()
    {
        return [
            'business_types'          => constants('PARTNER_BUSINESS_TYPES'),
            'smanager_business_types' => constants('PARTNER_SMANAGER_BUSINESS_TYPE'),
            'ownership_types'         => constants('PARTNER_OWNER_TYPES'),
            'business_categories'     => constants('PARTNER_BUSINESS_CATEGORIES'),
            'sectors'                 => constants('PARTNER_BUSINESS_SECTORS')
        ];
    }

    public static function keys()
    {
        return [
            'business_name',
            'business_type',
            'smanager_business_type',
            'ownership_type',
            'stock_price',
            'location',
            'establishment_year',
            'tin_no',
            'trade_license',
            'trade_license_issue_date',
            'yearly_income',
            'tin_certificate',
            'full_time_employee',
            'part_time_employee',
            'business_additional_information',
            'last_six_month_sales_information',
            'annual_cost',
            'fixed_asset',
            'security_check',
            'business_category',
            'sector',
            'industry_and_business_nature',
            'date_of_establishment',
            'strategic_partner',
            'short_name'
        ];
    }

    public static function validator($version)
    {
        return [
                   'business_type'      => 'string',
                   'location'           => 'string',
                   'establishment_year' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                   'full_time_employee' => 'numeric'
               ] + (($version == 2) ? ['loan_type' => 'sometimes|required|in:' . implode(',', LoanTypes::get())] : []);
    }

    public static function agreements()
    {
        $partner_portal = env('SHEBA_PARTNER_URL');
        return ['licence_agreement' => "$partner_portal/api/micro-loan-terms", 'ipdc_data_agreement' => "$partner_portal/api/micro-loan-report-share"];

    }
}
