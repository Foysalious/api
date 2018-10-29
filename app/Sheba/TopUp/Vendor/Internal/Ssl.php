<?php

namespace Sheba\TopUp\Vendor\Internal;

use Sheba\TopUp\Vendor\Response\SslResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use SoapClient;
use SoapFault;

class Ssl
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
     * @param $mobile_number
     * @param $amount
     * @param $type
     * @return TopUpResponse
     * @throws SoapFault
     */
    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        try {
            ini_set("soap.wsdl_cache_enabled", '0'); // disabling WSDL cache
            $client = new SoapClient($this->topUpUrl);
            $guid = randomString(20, 1, 1);
            $operator_id = $this->getOperatorId($mobile_number);
            $connection_type = $type;
            $sender_id = "redwan@sslwireless.com";
            $priority = 1;
            $s_url = "http://192.168.69.178:88/virtualrecharge/client/reply.php?s=1";
            $f_url = "http://192.168.69.178:88/virtualrecharge/client/reply.php?f=1";
            $calling_method = "POST";
            $create_recharge_response = $client->CreateRecharge($this->clientId, $this->clientPassword, $guid, $operator_id,
                $mobile_number, $amount, $connection_type, $sender_id, $priority, $s_url, $f_url, $calling_method);
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

    private function getOperatorId($mobile_number)
    {
        $mobile_number = formatMobile($mobile_number);
        if (preg_match("/^(\+88017)/", $mobile_number)) {
            return 1;
        } elseif (preg_match("/^(\+88019)/", $mobile_number)) {
            return 2;
        } elseif (preg_match("/^(\+88015)/", $mobile_number)) {
            return 5;
        } else {
            throw new \InvalidArgumentException('Invalid Mobile for ssl topup.');
        }
    }
}