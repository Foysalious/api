<?php namespace Sheba\TopUp\Gateway\Pretups\Operator;

use Sheba\TopUp\Gateway\FailedReason;
use Sheba\TopUp\Gateway\FailedReason\BanglalinkFailedReason;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Gateway\Pretups\Pretups;

class Banglalink extends Pretups implements Gateway
{
    CONST SHEBA_COMMISSION = 3.0;

    protected function getUrl()
    {
        $base_url = config('topup.bl.url');
        $login = config('topup.bl.login_id');
        $password = config('topup.bl.password');
        $gateway_code = config('topup.bl.gateway_code');
        $url = "$base_url?LOGIN=$login&PASSWORD=$password&REQUEST_GATEWAY_CODE=$gateway_code";
        $url .= "&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";
        return $url;
    }

    protected function getMid()
    {
        return config('topup.bl.mid');
    }

    protected function getPin()
    {
        return config('topup.bl.pin');
    }

    protected function getEXTNWCODE()
    {
        return "BD";
    }

    protected function getLanguage1()
    {
        return "0";
    }

    protected function getLanguage2()
    {
        return "1";
    }

    protected function getSelectors()
    {
        return [
            'prepaid' => '', 'postpaid' => '1'
        ];
    }

    protected function getAmountMultiplier()
    {
        return 100;
    }

    protected function getVPNUrl()
    {
        return "https://bl-vpn.sheba.xyz";
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::BANGLALINK;
    }

    public function getFailedReason(): FailedReason
    {
        return new BanglalinkFailedReason();
    }
}
