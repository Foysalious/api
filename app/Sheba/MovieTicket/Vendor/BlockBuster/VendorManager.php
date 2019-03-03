<?php namespace Sheba\MovieTicket\Vendor\BlockBuster;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\MovieTicket\Vendor\Vendor;

class VendorManager
{
    /**
     * @var Vendor $vendor
     */
    private $vendor;

    /**
     * @return mixed
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param Vendor $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    public function initVendor()
    {
        $this->vendor->init();
    }

    /**
     * @param $action
     * @param array $params
     * @throws GuzzleException
     */
    public function get($action, $params = [])
    {
        try {
            return $this->vendor->get($action,$params);
        } catch (GuzzleException $e) {
            throw $e;
        }

    }
}