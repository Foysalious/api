<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Exception;
use Sheba\TopUp\Vendor\Internal\PaywellClient;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Paywell implements Gateway
{
    private $paywell;
    CONST SHEBA_COMMISSION = 0.0;

    public function __construct(PaywellClient $paywell)
    {
        $this->paywell = $paywell;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        return $this->paywell->recharge($topup_order);
    }

    public function getInitialStatus()
    {
        return self::getInitialStatusStatically();
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public static function getInitialStatusStatically()
    {
        return config('topup.status.successful.sheba');
    }
}
