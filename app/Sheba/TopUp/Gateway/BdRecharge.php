<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Sheba\TopUp\Exception\TopUpStillNotResolvedException;
use Sheba\TopUp\Exception\UnknownIpnStatusException;
use Sheba\TopUp\Vendor\Response\Ipn\BdRecharge\BdRechargeFailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\BdRecharge\BdRechargeSuccessResponse;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Gateway\Clients\BdRechargeClient;
use Sheba\TopUp\Gateway\FailedReason\BdRechargeFailedReason;
use Sheba\TopUp\Vendor\Response\BdRechargeResponse;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyServerError;

class BdRecharge implements Gateway, HasIpn
{
    CONST SHEBA_COMMISSION = 0.0;
    CONST SUCCESS = 1;
    CONST FAILED = 2;

    private $client;

    public function __construct(BdRechargeClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $response = new BdRechargeResponse();
        $response->setResponse($this->client->recharge($topup_order));
        return $response;
    }

    public function getInitialStatus(): string
    {
        return self::getInitialStatusStatically();
    }

    public static function getInitialStatusStatically(): string
    {
        return Statuses::ATTEMPTED;
    }

    public function getShebaCommission(): float
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName(): string
    {
        return Names::BD_RECHARGE;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return IpnResponse
     * @throws TPProxyServerError | TopUpStillNotResolvedException | GatewayTimeout
     */
    public function enquire(TopUpOrder $topup_order): IpnResponse
    {
        $response = $this->client->enquiry($topup_order);
        /** @var IpnResponse $ipn_response */
        $ipn_response = null;
        $data = property_exists($response, 'data') ? $response->data : $response;
        $status = $data->status;
        if ($status == 'success' ) {
            $ipn_response = app(BdRechargeSuccessResponse::class);
        } else if ($status == 'failed' || $status == 400) {
            $ipn_response = app(BdRechargeFailResponse::class);
        } else if ($status == 'processing') {
            throw new TopUpStillNotResolvedException($response);
        }
        $ipn_response->setResponse($data);
        return $ipn_response;
    }

    public function getFailedReason(): FailedReason
    {
        return new BdRechargeFailedReason();
    }

    /**
     * @throws UnknownIpnStatusException
     */
    public function buildIpnResponse($request_data)
    {
        if( $request_data['status'] == 1) {
            return app(BdRechargeSuccessResponse::class);
        } elseif ($request_data['status'] == 2) {
            return app(BdRechargeFailResponse::class);
        }

        throw new UnknownIpnStatusException();
    }
}