<?php

namespace Sheba\TopUp\Vendor\Internal;

use Sheba\TopUp\TopUpResponse;

class Rax
{
    private $url;
    private $pin;
    private $mId;

    public function __construct()
    {
        $base_url = config('topup.robi.url');
        $login = config('topup.robi.login_id');
        $password = config('topup.robi.password');

        $this->url = "$base_url?LOGIN=$login&PASSWORD=$password";
        $this->url = "&REQUEST_GATEWAY_CODE=EXTGW&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";

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

    /**
     * @param $mobile_number
     * @param $amount
     * @param $type
     * @return TopUpResponse
     */
    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        $response = $this->call($this->makeInputString($mobile_number, $amount, $type));
        dd($response);
        return new TopUpResponse();
    }

    private function makeInputString($mobile_number, $amount, $type)
    {
        $input = '<?xml version="1.0"?><COMMAND>';
        $input .= '<TYPE>EXRCTRFREQ</TYPE>';
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
        $input .= '<SELECTOR>1</SELECTOR>';
        $input .= '</COMMAND>';
        return $input;
    }

    private function call($input)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml', 'Connection: close']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=$input");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $data = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        return simplexml_load_string($data);
    }
}