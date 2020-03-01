<?php namespace Sheba\TopUp\Gateway;

use Sheba\TopUp\Gateway\Pretups\Operator\Airtel;
use Sheba\TopUp\Gateway\Pretups\Operator\Robi;
use Sheba\TopUp\Gateway\Pretups\Operator\Banglalink;
use Sheba\TopUp\Vendor\VendorFactory;

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
     * @return Gateway
     */
    public function get()
    {
        if ($this->gatewayName == Names::PRETUPS) {
            $vendor_factory = new VendorFactory();
            if ($this->vendorId == $vendor_factory::BANGLALINK) return app(Banglalink::class);
            if ($this->vendorId == $vendor_factory::ROBI) return app(Robi::class);
            if ($this->vendorId == $vendor_factory::AIRTEL) return app(Airtel::class);
        }
        return app(Ssl::class);
    }
}