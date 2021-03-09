<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Sheba\Dal\TopupOrder\Statuses;
use Exception;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Vendor\Internal\SslVrClient;
use Sheba\TopUp\Vendor\Response\SslResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Ssl implements Gateway
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
            'guid' => $topup_order->getGatewayRefId(),
            'payee_mobile' => $topup_order->payee_mobile,
            'operator_id' => $this->getOperatorId($topup_order->payee_mobile),
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
    public function getRecharge($guid, $vr_guid)
    {
        return $this->sslVrClient->call([
            'action' => SslVrClient::VR_PROXY_STATUS_ACTION,
            'guid' => $guid,
            'vr_guid' => $vr_guid
        ]);
    }

    private function getOperatorId($mobile_number)
    {
        $mobile_number = formatMobile($mobile_number);

        if (preg_match("/^(\+88017)/", $mobile_number) || preg_match("/^(\+88013)/", $mobile_number)) return 1;
        if (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) return 2;
        if (preg_match("/^(\+88018)/", $mobile_number)) return 3;
        if (preg_match("/^(\+88016)/", $mobile_number)) return 6;
        if (preg_match("/^(\+88015)/", $mobile_number)) return 5;

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
}
