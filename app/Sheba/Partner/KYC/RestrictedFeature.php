<?php namespace Sheba\Partner\KYC;

class RestrictedFeature
{
    public static function get()
    {
        return [
            'RechargeActivity',
            'PaymentLinkShareActivity',
            'CreateLinkActivity',
            'PosProductLinkShareActivity',
            'PosPaymentLinkShareActivity',
            'MonthlyEmiListActivity',
            'EmiBalanceSetActivity',
            'WithdrawRequestActivity',
            'HomeLandingActivity',
            'MyCompanyActivity',
            'CurrentPackageActivity',
            'DueCreateLinkActivity',
            'DCShareOptionsActivity',
            'CreateCustomLinkActivity'
        ];
    }

}
