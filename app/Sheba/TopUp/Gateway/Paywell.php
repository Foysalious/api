<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Exception;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
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
     * @throws GatewayTimeout
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        return $this->paywell->recharge($topup_order);
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::PAYWELL;
    }
}
