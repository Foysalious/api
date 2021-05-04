<?php namespace Sheba\TopUp\OTF;

use Sheba\Dal\TopUpOTFSettings\Contract as TopUpOTFSettingsRepo;
use Sheba\Dal\TopUpVendorOTF\Contract as TopUpVendorOTFRepo;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\Vendor\VendorFactory;

class OtfAmount
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
     * @return mixed
     */
    private function otfSettings()
    {
        return $this->otfSettings->builder()->where([
            ['type', $this->agent],
            ['applicable_gateways', '<>', 'null']
        ])->first();
    }

    /**
     * @return mixed
     */
    private function otfList()
    {
        return $this->vendorOtf->builder()->where([
            ['status', 'Active']
        ])->get();
    }
}