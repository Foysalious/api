<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Sheba\Logs\ErrorLog;
use Sheba\TopUp\Vendor\Response\SslResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use SoapClient;
use SoapFault;

class SslClient
{
    private $clientId;
    private $clientPassword;
    private $topUpUrl;

    /** @var SoapClient */
    private $soapClient;

    public function __construct()
    {
        $this->clientId = config('ssl.topup_client_id');
        $this->clientPassword = config('ssl.topup_client_password');
        $this->topUpUrl = config('ssl.topup_url');
    }

    /**
     * @throws SoapFault
     */
    public function setSoapClient()
    {
        ini_set("soap.wsdl_cache_enabled", '0'); // disabling WSDL cache
        $this->soapClient = new SoapClient($this->topUpUrl);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $ssl_response = new SslResponse();
        try {
            $this->setSoapClient();
            $guid = randomString(20, 1, 1);
            $mobile = $topup_order->payee_mobile;
            $operator_id = $this->getOperatorId($mobile);
            $connection_type = $topup_order->payee_mobile_type;
            $sender_id = "redwan@sslwireless.com";
            $priority = 1;
            $s_url = config('sheba.api_url') . '/v2/top-up/success/ssl';
            $f_url = config('sheba.api_url') . '/v2/top-up/fail/ssl';
            $calling_method = "GET";
            $create_recharge_response = $this->soapClient->CreateRecharge($this->clientId, $this->clientPassword, $guid, $operator_id, $mobile, $topup_order->amount, $connection_type, $sender_id, $priority, $s_url, $f_url, $calling_method);
            $vr_guid = $create_recharge_response->vr_guid;
            $recharge_response = $this->soapClient->InitRecharge($this->clientId, $this->clientPassword, $guid, $vr_guid);
            $recharge_response->guid = $guid;
            $ssl_response->setResponse($recharge_response);
        } catch (SoapFault $exception) {
            (new ErrorLog())->setException($exception)->send();
        }
        return $ssl_response;
    }

    /**
     * @return mixed
     * @throws SoapFault
     */
    public function getBalance()
    {
        try {
            $this->setSoapClient();
            $response = $this->soapClient->GetBalanceInfo($this->clientId);
            return $response;
        } catch (SoapFault $exception) {
            throw $exception;
        }
    }

    /**
     * @param $guid
     * @param $vr_guid
     * @return mixed
     * @throws SoapFault
     */
    public function getRecharge($guid, $vr_guid)
    {
        try {
            $this->setSoapClient();
            $response = $this->soapClient->QueryRechargeStatus($this->clientId, $guid, $vr_guid);
            return $response;
        } catch (SoapFault $exception) {
            throw $exception;
        }
    }

    private function getOperatorId($mobile_number)
    {
        $mobile_number = formatMobile($mobile_number);
        if (preg_match("/^(\+88017)/", $mobile_number) || preg_match("/^(\+88013)/", $mobile_number)) {
            return 1;
        } elseif (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) {
            return 2;
        } elseif (preg_match("/^(\+88018)/", $mobile_number)) {
            return 3;
        } elseif (preg_match("/^(\+88016)/", $mobile_number)) {
            return 6;
        } elseif (preg_match("/^(\+88015)/", $mobile_number)) {
            return 5;
        } else {
            throw new \InvalidArgumentException('Invalid Mobile for ssl topup.');
        }
    }
}
