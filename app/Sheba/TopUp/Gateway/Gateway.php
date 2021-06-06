<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Exception;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Exception\PaywellTopUpStillNotResolved;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

interface Gateway
{
    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     * @throws GatewayTimeout
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse;

    public function getShebaCommission();

    public function getName();

    /**
     * @param TopUpOrder $topup_order
     * @throws PaywellTopUpStillNotResolved
     * @return IpnResponse
     */
    public function enquireIpnResponse(TopUpOrder $topup_order): IpnResponse;
}
