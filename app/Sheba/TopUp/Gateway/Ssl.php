<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Exception;
use Sheba\TopUp\Vendor\Internal\SslClient;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Ssl implements Gateway
{
    private $ssl;
    CONST SHEBA_COMMISSION = 0.0;

    public function __construct(SslClient $ssl)
    {
        $this->ssl = $ssl;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        return $this->ssl->recharge($topup_order);
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
        return config('topup.status.pending.sheba');
    }
}
