<?php


namespace App\Jobs\TicketVendorBalanceAlert;

use Sheba\Transport\Bus\Vendor\VendorFactory;

class Transport
{
    private $vendorId;

    public function getVendor($vendor_id)
    {
        $vendor = app(VendorFactory::class);
        return $vendor->getById($vendor_id);
    }



}