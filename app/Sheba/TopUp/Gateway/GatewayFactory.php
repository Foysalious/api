<?php namespace Sheba\TopUp\Gateway;

use Sheba\TopUp\Gateway\Pretups\Operator\Airtel;
use Sheba\TopUp\Gateway\Pretups\Operator\Robi;
use Sheba\TopUp\Gateway\Pretups\Operator\Banglalink;
use Sheba\Dal\TopupVendor\Gateway;
use Sheba\TopUp\Gateway\Gateway as TopupGateway;
class GatewayFactory
{

    private $vendorId;
    private $gatewayName;

    public function setVendorId($vednor_id)
    {
        $this->vendorId = $vednor_id;
        return $this;
    }


    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;
        return $this;
    }

    /**
     * @return TopupGateway
     */
    public function get()
    {
        if ($this->gatewayName == Gateway::BANGLALINK) return app(Banglalink::class);
        if ($this->gatewayName == Gateway::ROBI) return app(Robi::class);
        if ($this->gatewayName == Gateway::AIRTEL) return app(Airtel::class);
        else return app(Ssl::class);
    }
}