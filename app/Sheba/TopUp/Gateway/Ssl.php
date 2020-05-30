<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Sheba\Dal\TopupOrder\Statuses;
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

    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        return $this->ssl->recharge($topup_order);
    }

    public function getInitialStatus()
    {
        return Statuses::PENDING;
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getBalance()
    {
        return $this->ssl->getBalance()->available_credit;
    }
}
