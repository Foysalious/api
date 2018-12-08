<?php

namespace Sheba\TopUp\Vendor\Internal;

use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\SslResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use SoapClient;
use SoapFault;

class SslClient
{
    private $clientId;
    private $clientPassword;
    private $topUpUrl;

    public function __construct()
    {
        $this->clientId = config('ssl.topup_client_id');
        $this->clientPassword = config('ssl.topup_client_password');
        $this->topUpUrl = config('ssl.topup_url');
    }

    /**
     * @param TopUpRequest $top_up_request
     * @return TopUpResponse
     * @throws SoapFault
     */
    public function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        try {
            ini_set("soap.wsdl_cache_enabled", '0'); // disabling WSDL cache
            $client = new SoapClient($this->topUpUrl);
            $guid = randomString(20, 1, 1);
            $mobile = $top_up_request->getMobile();
            $operator_id = $this->getOperatorId($mobile);
            $connection_type = $top_up_request->getType();
            $sender_id = "redwan@sslwireless.com";
            $priority = 1;
            $s_url = "http://192.168.69.178:88/virtualrecharge/client/reply.php?s=1";
            $f_url = config('sheba.api_url') . '/v2/top-up/fail/ssl';
            $calling_method = "GET";
            $create_recharge_response = $client->CreateRecharge($this->clientId, $this->clientPassword, $guid, $operator_id,
                $mobile, $top_up_request->getAmount(), $connection_type, $sender_id, $priority, $s_url, $f_url, $calling_method);
            $vr_guid = $create_recharge_response->vr_guid;
            $recharge_response = $client->InitRecharge($this->clientId, $this->clientPassword, $guid, $vr_guid);
            $recharge_response->guid = $guid;
            $ssl_response = new SslResponse();
            $ssl_response->setResponse($recharge_response);
            return $ssl_response;
        } catch (SoapFault $exception) {
            throw $exception;
        }
    }

    public function getBalance()
    {
        try {
            ini_set("soap.wsdl_cache_enabled", '0'); // disabling WSDL cache
            $client = new SoapClient($this->topUpUrl);
            $response = $client->GetBalanceInfo($this->clientId);
            return $response;
        } catch (SoapFault $exception) {
            throw $exception;
        }
    }

    public function getRecharge($guid, $vr_guid)
    {
        try {
            ini_set("soap.wsdl_cache_enabled", '0'); // disabling WSDL cache
            $client = new SoapClient($this->topUpUrl);
            $response = $client->QueryRechargeStatus($this->clientId, $guid, $vr_guid);
            return $response;
        } catch (SoapFault $exception) {
            throw $exception;
        }
    }

    private function getOperatorId($mobile_number)
    {
        $mobile_number = formatMobile($mobile_number);
        if (preg_match("/^(\+88017)/", $mobile_number)) {
            return 1;
        } elseif (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) {
            return 2;
        } elseif (preg_match("/^(\+88015)/", $mobile_number)) {
            return 5;
        } elseif (preg_match("/^(\+88013)/", $mobile_number)) {
            return 1;
        } else {
            throw new \InvalidArgumentException('Invalid Mobile for ssl topup.');
        }
    }
}