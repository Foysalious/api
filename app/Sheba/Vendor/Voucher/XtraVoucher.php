<?php namespace Sheba\Vendor\Voucher;


use App\Models\Tag;
use App\Models\Vendor;

class XtraVoucher
{
    private $vendor;

    public function __construct()
    {
        $this->vendor = Vendor::find(config('vendor.xtra_vendor_id'));
    }

    public function getVendor()
    {
        $this->vendor['vendor_contribution'] = config('vendor.xtra_vendor_contribution_in_percentage');
        $this->vendor['sheba_contribution'] = 100 - config('vendor.xtra_vendor_contribution_in_percentage');
        $this->vendor['default_title'] = config('vendor.xtra_promo_default_title');
        $this->vendor['default_tag'] = Tag::find(config('vendor.xtra_vendor_tag_id'));
        return $this->vendor;
    }
}
