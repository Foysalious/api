<?php namespace Sheba\TopUp\Vendor\Internal;

trait RobiAxiata
{
    use Pretups;

    private function getUrl()
    {
        $base_url = config('topup.robi.url');
        $login = config('topup.robi.login_id');
        $password = config('topup.robi.password');
        $gateway_code = config('topup.robi.gateway_code');
        $url = "$base_url?LOGIN=$login&PASSWORD=$password&REQUEST_GATEWAY_CODE=$gateway_code";
        $url .= "&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";
        return $url;
    }

    private function getVPNUrl()
    {
        return "http://robi-vpn.dev-sheba.xyz";
    }

    private function getEXTNWCODE()
    {
        return "AK";
    }

    private function getLanguage1()
    {
        return "1";
    }

    private function getLanguage2()
    {
        return "0";
    }

    private function getSelectors()
    {
        return [
            'prepaid' => '1',
            'postpaid' => '2'
        ];
    }

    private function getAmountMultiplier()
    {
        return 1;
    }
}
