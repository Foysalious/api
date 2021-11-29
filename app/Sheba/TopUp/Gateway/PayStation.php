<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Exception\PayStationNotWorkingException;
use Sheba\TopUp\Gateway\FailedReason\PayStationFailedReason;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\PayStationResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class PayStation implements Gateway
{
    CONST SHEBA_COMMISSION = 0.0;
    CONST SUCCESS = 1;
    CONST FAILED = 2;

    /** @var HttpClient */
    private $httpClient;

    private $rechargeUrl;
    private $userName;
    private $password;

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;

        $this->rechargeUrl = config('topup.pay_station.recharge_url');
        $this->userName = config('topup.pay_station.user_name');
        $this->password = config('topup.pay_station.password');
    }

    /**
     * @throws GatewayTimeout
     * @throws GuzzleException
     * @throws PayStationNotWorkingException
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $response = new PayStationResponse();
        $response->setResponse($this->call($this->makeUrl($topup_order)));
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

    private function getConnectionType($connection_type, $vendor_id)
    {
        if ($vendor_id == 7) return "Skitto";
        if ($connection_type == "prepaid") return "Pre-paid";
        if ($connection_type == "postpaid") return "Post-paid";

        throw new InvalidArgumentException('Invalid connection type for pay station topup.');
    }

    /**
     * @throws GatewayTimeout
     * @throws GuzzleException
     * @throws PayStationNotWorkingException
     */
    private function call($url)
    {
        try {
            $result = $this->httpClient->get($url, [
                'timeout' => 60,
                'read_timeout' => 60,
                'connect_timeout' => 60
            ]);
        } catch (ConnectException $e) {
            if (isTimeoutException($e)) throw new GatewayTimeout($e->getMessage());
            throw $e;
        }

        $response = $result->getBody()->getContents();
        if (!$response) throw new PayStationNotWorkingException();
        return json_decode($response);
    }
}