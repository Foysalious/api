<?php

namespace Sheba\TopUp\Vendor\Internal;

use GuzzleHttp\Client;
use Sheba\TopUp\Vendor\Response\RaxResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Rax
{
    private $url;
    private $pin;
    private $mId;
    private $proxyUrl;
    private $httpClient;

    public function __construct(Client $client)
    {
        $base_url = config('topup.robi.url');
        $login = config('topup.robi.login_id');
        $password = config('topup.robi.password');
        $this->url = "$base_url?LOGIN=$login&PASSWORD=$password&REQUEST_GATEWAY_CODE=SHEBAEXTGW&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";
        $this->proxyUrl = config('topup.robi.proxy_url');
        $this->httpClient = $client;
    }

    public function setPin($pin)
    {
        $this->pin = $pin;
        return $this;
    }

    public function setMId($mid)
    {
        $this->mId = $mid;
        return $this;
    }

    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        $response = $this->call($this->makeInputString(getOriginalMobileNumber($mobile_number), $amount, $type));
        $rax_response = new RaxResponse();
        $rax_response->setResponse($response);
        return $rax_response;
    }

    private function makeInputString($mobile_number, $amount, $type)
    {
        $input = '<?xml version="1.0"?><COMMAND>';
        $input .= '<DATE></DATE>';
        $input .= '<EXTNWCODE>AK</EXTNWCODE>';
        $input .= "<MSISDN>$this->mId</MSISDN>";
        $input .= "<PIN>$this->pin</PIN>";
        $input .= '<LOGINID></LOGINID>';
        $input .= '<PASSWORD></PASSWORD>';
        $input .= '<EXTCODE></EXTCODE>';
        $input .= '<EXTREFNUM></EXTREFNUM>';
        $input .= "<MSISDN2>$mobile_number</MSISDN2>";
        $input .= "<AMOUNT>$amount</AMOUNT>";
        $input .= '<LANGUAGE1>1</LANGUAGE1>';
        $input .= '<LANGUAGE2>0</LANGUAGE2>';
        $input .= $this->calculateTypeParams($type);
        $input .= '</COMMAND>';
        return $input;
    }

    private function call($input)
    {
        $result = $this->httpClient->request('POST', $this->proxyUrl, [
            'form_params' => [
                'url' => $this->url,
                'input' => $input
            ]
        ]);

        return simplexml_load_string($result->getBody()->getContents());

        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml', 'Connection: close']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=$input");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $data = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        return simplexml_load_string($data);*/
    }

    private function calculateTypeParams($type)
    {
        return $type == 'prepaid' ? '<TYPE>EXRCTRFREQ</TYPE><SELECTOR>1</SELECTOR>' : '<TYPE>EXPPBREQ</TYPE><SELECTOR>2</SELECTOR>';
    }
}