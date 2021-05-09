<?php namespace Sheba\TopUp\FailedReason;

use App\Models\TopUpOrder;
use App\Sheba\TopUp\FailedReason\BdRechargeFailedReason;
use Sheba\TopUp\Gateway\Names;

class FailedReasonFactory
{
    public static function make(TopUpOrder $top_up_order)
    {
        if ($top_up_order->gateway == Names::SSL) return new SslFailedReason();
        if ($top_up_order->gateway == Names::ROBI) return new RobiFailedReason();
        if ($top_up_order->gateway == Names::BANGLALINK) return new BanglalinkFailedReason();
        if ($top_up_order->gateway == Names::AIRTEL) return new AirtelFailedReason();
        if ($top_up_order->gateway == Names::PAYWELL) return new PaywellFailedReason();
        if ($top_up_order->gateway == Names::BD_RECHARGE) return new BdRechargeFailedReason();
    }
}