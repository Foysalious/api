<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;


trait RobiAxiata
{
    protected function getUrl()
    {
        $base_url = config('topup.robi.url');
        $login = config('topup.robi.login_id');
        $password = config('topup.robi.password');
        $gateway_code = config('topup.robi.gateway_code');
        $url = "$base_url?LOGIN=$login&PASSWORD=$password&REQUEST_GATEWAY_CODE=$gateway_code";
        $url .= "&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";
        return $url;
    }

    protected function getVPNUrl()
    {
        return "http://robi-vpn.dev-sheba.xyz";
    }

    protected function getEXTNWCODE()
    {
        return "AK";
    }

    protected function getLanguage1()
    {
        return "1";
    }

    protected function getLanguage2()
    {
        return "0";
    }

    protected function getSelectors()
    {
        return [
            'prepaid' => '1',
            'postpaid' => '2'
        ];
    }

    protected function getAmountMultiplier()
    {
        return 1;
    }
}
