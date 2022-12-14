<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Exception;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Exception\TopUpStillNotResolvedException;
use Sheba\TopUp\Gateway\Clients\PaywellClient;
use Sheba\TopUp\Gateway\FailedReason\PaywellFailedReason;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Paywell\PaywellFailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Paywell\PaywellSuccessResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyServerError;

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

    public function getShebaCommission(): float
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName(): string
    {
        return Names::PAYWELL;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return IpnResponse
     * @throws TPProxyServerError | TopUpStillNotResolvedException
     */
    public function enquire(TopUpOrder $topup_order): IpnResponse
    {
        $response = $this->paywell->enquiry($topup_order);

        /** @var IpnResponse $ipn_response */
        if ($response->status_code == "200") {
            $ipn_response = app(PaywellSuccessResponse::class);
        } else if ($response->status_code != "100") {
            $ipn_response = app(PaywellFailResponse::class);
        } else {
            throw new TopUpStillNotResolvedException($response);
        }
        $ipn_response->setResponse($response);
        return $ipn_response;
    }

    public function getFailedReason(): FailedReason
    {
        return new PaywellFailedReason();
    }
}
