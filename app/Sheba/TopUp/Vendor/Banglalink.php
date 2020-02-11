<?php namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Internal\Pretups;
use Sheba\TopUp\Vendor\Internal\Ssl;

class Banglalink extends Vendor
{
    use Ssl;
    /*use Pretups;

    private function getUrl()
    {
        $base_url = config('topup.bl.url');
        $login = config('topup.bl.login_id');
        $password = config('topup.bl.password');
        $gateway_code = config('topup.bl.gateway_code');
        $url = "$base_url?LOGIN=$login&PASSWORD=$password&REQUEST_GATEWAY_CODE=$gateway_code";
        $url .= "&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";
        return $url;
    }

    private function getMid()
    {
        return config('topup.bl.mid');
    }

    private function getPin()
    {
        return config('topup.bl.pin');
    }

    private function getEXTNWCODE()
    {
        return "BD";
    }

    private function getLanguage1()
    {
        return "0";
    }

    private function getLanguage2()
    {
        return "1";
    }

    private function getSelectors()
    {
        return [
            'prepaid' => '', 'postpaid' => '1'
        ];
    }

    private function getAmountMultiplier()
    {
        return 100;
    }

    private function getVPNServer()
    {
        return "https://api.sheba.xyz";
    }*/
}
