<?php namespace Sheba\TopUp\OTF;

use Sheba\Dal\TopUpOTFSettings\Contract as TopUpOTFSettingsRepo;
use Sheba\Dal\TopUpVendorOTF\Contract as TopUpVendorOTFRepo;
use Sheba\TopUp\Vendor\VendorFactory;
use Exception;

class OtfAmount
{
    const BUSINESS_AGENT = "App\\Models\\Customer";

    /** @var TopUpVendorOTFRepo */
    private $vendorOtf;
    /** @var TopUpOTFSettingsRepo */
    private $otfSettings;
    /** @var VendorFactory */
    private $vendorFactory;

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
     * @throws Exception
     */
    public function get()
    {
        $otf_lists = $this->otfList();

        return $this->formatData($otf_lists);
    }

    /**
     * @param $otf_lists
     * @return array
     * @throws Exception
     */
    private function formatData($otf_lists)
    {
        $formatted_data = [];
        foreach ($otf_lists as $otf_list) {
            $vendor = $this->vendor($otf_list->topup_vendor_id);
            $formatted_data[] = [
                "operator_id" => $vendor->id,
                "operator_name" => $vendor->name,
                "amount" => $otf_list->amount,
                "connection_type" => $otf_list->sim_type,
                "offer_type" => $otf_list->type,
                "offer_title" => $otf_list->name_en,
                "offer_description" => $otf_list->description
            ];
        }
        return $formatted_data;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function otfList()
    {
        return $this->vendorOtf->builder()->whereIn('topup_vendor_id', $this->vendorIds())->where([
            ['status', 'Active']
        ])->orderBy('amount', 'ASC')->get();
    }


    /**
     * @return array
     * @throws Exception
     */
    private function vendorIds()
    {
        $otf_settings = $this->otfSettings();

        $vendor_ids = [];
        foreach ($otf_settings as $otf_setting) {
            $vendor = $this->vendor($otf_setting->topup_vendor_id);
            if (in_array($vendor->gateway, json_decode($otf_setting->applicable_gateways))) {
                $vendor_ids[] = $vendor->id;
            }
        }
        return $vendor_ids;
    }

    /**
     * @param $vendor_id
     * @return mixed
     * @throws Exception
     */
    private function vendor($vendor_id)
    {
        return $this->vendorFactory->getById($vendor_id)->getModel();
    }

    /**
     * @return mixed
     */
    private function otfSettings()
    {
        return $this->otfSettings->builder()->where([
            ['type', self::BUSINESS_AGENT],
            ['applicable_gateways', '<>', 'null']
        ])->get();
    }
}