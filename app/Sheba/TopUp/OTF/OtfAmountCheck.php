<?php namespace Sheba\TopUp\OTF;

use Sheba\Dal\TopUpOTFSettings\Contract as TopUpOTFSettingsRepo;
use Sheba\Dal\TopUpVendorOTF\Contract as TopUpVendorOTFRepo;
use Sheba\TopUp\Vendor\VendorFactory;
use Sheba\TopUp\TopUpAgent;
use Exception;

class OtfAmountCheck
{
    /** @var TopUpVendorOTFRepo */
    private $vendorOtf;
    /** @var TopUpOTFSettingsRepo */
    private $otfSettings;
    /** @var VendorFactory */
    private $vendorFactory;
    private $amount;
    private $type;
    private $vendor;
    private $vendorId;
    private $agent;

    /**
     * OtfAmountCheck constructor.
     * @param TopUpVendorOTFRepo $vendor_otf
     * @param TopUpOTFSettingsRepo $otf_settings
     * @param VendorFactory $vendor_factory
     */
    public function __construct(TopUpVendorOTFRepo $vendor_otf, TopUpOTFSettingsRepo $otf_settings, VendorFactory $vendor_factory)
    {
        $this->vendorOtf = $vendor_otf;
        $this->otfSettings = $otf_settings;
        $this->vendorFactory = $vendor_factory;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return (double)$this->amount;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = (double)$amount;
        return $this;
    }

    /**
     * operator gp|robi etc.
     * @param $vendor_id
     * @return $this
     * @throws Exception
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        $this->vendor = $this->vendorFactory->getById($this->vendorId)->getModel();
        return $this;
    }

    /**
     * operator gp|robi etc.
     * @param $vendor
     * @return $this
     * @throws Exception
     */
    public function setVendor($vendor)
    {
        $this->vendor = $this->vendorFactory->getByName($vendor)->getModel();
        $this->vendorId = $this->vendor->id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * connection_type prepaid|postpaid
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent)
    {
        #$this->agent = $agent;
        $this->agent = "App\\Models\\Customer";
        return $this;
    }

    /**
     * @return bool
     */
    public function isAmountInOtf()
    {
        if ($this->otfSettings() && $this->isGatewayExist() && !$this->otfList()->isEmpty())
            return true;
        return false;
    }

    /**
     * @return mixed
     */
    private function otfSettings()
    {
        return $this->otfSettings->builder()->where([
            ['topup_vendor_id', $this->vendorId],
            ['type', $this->agent],
            ['applicable_gateways', '<>', 'null']
        ])->first();
    }

    /**
     * @return bool
     */
    private function isGatewayExist()
    {
        return in_array($this->vendor->gateway, json_decode($this->otfSettings()->applicable_gateways));
    }

    /**
     * @return mixed
     */
    private function otfList()
    {
        return $this->vendorOtf->builder()->where([
            ['topup_vendor_id', $this->vendorId],
            ['sim_type', 'like', '%' . ucfirst($this->type) . '%'],
            ['status', 'Active'],
            ['amount', $this->amount]
        ])->get();
    }
}