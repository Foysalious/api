<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use InvalidArgumentException;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Exception\PayStationNotWorkingException;
use Sheba\TopUp\Gateway\FailedReason\PayStationFailedReason;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\PayStationResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPProxyServerTimeout;
use Sheba\TPProxy\TPRequest;

class PayStation implements Gateway
{
    CONST SHEBA_COMMISSION = 0.0;
    CONST SUCCESS = 1;
    CONST FAILED = 2;

    /** @var TPProxyClient */
    private $tpClient;

    private $rechargeUrl;
    private $userName;
    private $password;

    public function __construct(TPProxyClient $client)
    {
        $this->tpClient = $client;

        $this->rechargeUrl = config('topup.pay_station.recharge_url');
        $this->userName = config('topup.pay_station.user_name');
        $this->password = config('topup.pay_station.password');
    }

    /**
     * @throws GatewayTimeout
     * @throws PayStationNotWorkingException
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $api_response = $this->call($this->makeUrl($topup_order));
        dump($api_response);

        $response = new PayStationResponse();
        $response->setResponse($api_response);
        return $response;
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::PAY_STATION;
    }

    public function enquireIpnResponse(TopUpOrder $topup_order): IpnResponse
    {
        // TODO: Implement enquireIpnResponse() method.
    }

    public function getInitialStatus()
    {
        return self::getInitialStatusStatically();
    }

    public static function getInitialStatusStatically()
    {
        return Statuses::ATTEMPTED;
    }

    public function getFailedReason(): FailedReason
    {
        return new PayStationFailedReason();
    }

    private function makeUrl(TopUpOrder $topup_order)
    {
        return $this->rechargeUrl
            . "?ExternalRecharge=Recharge"
            . "&phone=" . $topup_order->payee_mobile
            . "&operator_type=" . $this->getOperatorType($topup_order->vendor_id)
            . "&amount=" . $topup_order->amount
            . "&recharge_operator_type=" . $this->getConnectionType($topup_order->vendor_id, $topup_order->payee_mobile_type)
            . "&user_name=" . $this->userName
            . "&password=" . $this->password
            . "&ref=" . $topup_order->getGatewayRefId();
    }

    private function getOperatorType($vendor_id)
    {
        if ($vendor_id == 2) return 'RR';
        if ($vendor_id == 3) return 'RA';
        if ($vendor_id == 4) return 'RG';
        if ($vendor_id == 5) return 'RB';
        if ($vendor_id == 6) return 'RT';
        if ($vendor_id == 7) return 'RG';

        throw new InvalidArgumentException('Invalid operator for pay station topup.');
    }

    private function getConnectionType($vendor_id, $connection_type)
    {
        if ($vendor_id == 7) return "Skitto";
        if ($connection_type == "prepaid") return "Pre-paid";
        if ($connection_type == "postpaid") return "Post-paid";

        throw new InvalidArgumentException('Invalid connection type for pay station topup.');
    }

    /**
     * @throws GatewayTimeout
     * @throws PayStationNotWorkingException
     */
    private function call($url)
    {
        $tp_request = (new TPRequest())
            ->setMethod(TPRequest::METHOD_GET)
            ->setUrl($url)
            ->setTimeout(60);

        dump($tp_request);

        try {
            return $this->tpClient->call($tp_request);
        } catch (TPProxyServerTimeout $e) {
            throw new GatewayTimeout($e->getMessage());
        } catch (TPProxyServerError $e) {
            throw new PayStationNotWorkingException($e->getMessage());
        }
    }
}