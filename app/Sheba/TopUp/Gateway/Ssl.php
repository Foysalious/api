<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Exception;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Gateway\FailedReason\SslFailedReason;
use Sheba\TopUp\Gateway\Clients\SslVrClient;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Ssl\SslFailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Ssl\SslSuccessResponse;
use Sheba\TopUp\Vendor\Response\SslResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Ssl implements Gateway, HasIpn
{
    CONST SHEBA_COMMISSION = 0.0;

    /** @var SslVrClient  */
    private $sslVrClient;

    public function __construct(SslVrClient $ssl)
    {
        $this->sslVrClient = $ssl;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     * @throws GatewayTimeout
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $ssl_response = new SslResponse();
        $ssl_response->setResponse($this->sslVrClient->call([
            "action" => SslVrClient::VR_PROXY_RECHARGE_ACTION,
            'guid' => $this->getRefId($topup_order),
            'payee_mobile' => $topup_order->payee_mobile,
            'operator_id' => $this->getOperatorId($topup_order->vendor_id),
            'connection_type' => $topup_order->payee_mobile_type,
            'sender_id' => "redwan@sslwireless.com",
            'priority' => 1,
            'success_url' => config('sheba.api_url') . '/v2/top-up/success/ssl',
            'fail_url' => config('sheba.api_url') . '/v2/top-up/fail/ssl',
            'calling_method' => "GET",
            'amount' => $topup_order->amount
        ]));
        return $ssl_response;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getBalance()
    {
        return $this->sslVrClient->call(["action" => SslVrClient::VR_PROXY_BALANCE_ACTION]);
    }

    /**
     * @param $guid
     * @param $vr_guid
     * @return mixed
     * @throws Exception
     */
    public function getRecharge($guid, $vr_guid = null)
    {
        return $this->sslVrClient->call([
            'action' => SslVrClient::VR_PROXY_STATUS_ACTION,
            'guid' => $guid,
            'vr_guid' => $vr_guid
        ]);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return IpnResponse
     * @throws Exception
     */
    public function enquire(TopUpOrder $topup_order): IpnResponse
    {
        $response = $this->getRecharge($this->getRefId($topup_order));
        /** @var IpnResponse $ipn_response */
        $ipn_response = ($response && $response->recharge_status == 900) ?
            app(SslSuccessResponse::class) :
            app(SslFailResponse::class);

        $ipn_response->setResponse($response);

        return $ipn_response;
    }

    private function getRefId(TopUpOrder $topup_order)
    {
        return str_pad($topup_order->getGatewayRefId(), 20, '0', STR_PAD_LEFT);
    }

    private function getOperatorId($vendor_id)
    {
        if ($vendor_id == 2) return 3;
        if ($vendor_id == 3) return 6;
        if ($vendor_id == 4) return 1;
        if ($vendor_id == 5) return 2;
        if ($vendor_id == 6) return 5;
        if ($vendor_id == 7) return 13;
        
        throw new \InvalidArgumentException('Invalid Mobile for ssl topup.');
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::SSL;
    }

    public function getFailedReason(): FailedReason
    {
        return new SslFailedReason();
    }

    public function buildIpnResponse($request_data)
    {
        if( $request_data['is_from_success_url']) {
            return app(SslSuccessResponse::class);
        } else {
            return app(SslFailResponse::class);
        }
    }
}
