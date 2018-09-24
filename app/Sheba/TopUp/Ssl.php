<?php

namespace Sheba\TopUp;

use SoapClient;
use SoapFault;
use stdClass;

class Ssl
{
    public function recharge($mobile_number, $amount, $type)
    {
        try {
            $wsdl_path = "http://vrtest.sslwireless.com/?wsdl";
            ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
            $client = new SoapClient($wsdl_path);
            $client_id = 'vr_sslw';
            $client_pass = 'ZmVyZG91cw==';
            $guid = randomString(20, 1, 1);
            $operator_id = 1;
            $recipient_msisdn = $mobile_number;
            $connection_type = $type;
            $sender_id = "redwan@sslwireless.com";
            $priority = 1;
            $s_url = "http://192.168.69.178:88/virtualrecharge/client/reply.php?s=1";
            $f_url = "http://192.168.69.178:88/virtualrecharge/client/reply.php?f=1";
            $calling_method = "POST";
            $create_recharge_response = $client->CreateRecharge($client_id, $client_pass, $guid, $operator_id,
                $recipient_msisdn, $amount, $connection_type, $sender_id, $priority, $s_url, $f_url, $calling_method);
            $vr_guid = $create_recharge_response->vr_guid;
            $recharge_response = new stdClass();
            $recharge_response = $client->InitRecharge($client_id, $client_pass, $guid, $vr_guid);
            $recharge_response->guid = $guid;
            return array(
                'transaction_id' => $guid,
                'transaction_details' => $recharge_response
            );
        } catch ( SoapFault $exception ) {
            return null;
        }
    }
}