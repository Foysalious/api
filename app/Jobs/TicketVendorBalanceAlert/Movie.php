<?php


namespace App\Jobs\TicketVendorBalanceAlert;

use Sheba\MovieTicket\Vendor\VendorFactory;

class Movie
{
    public function getVendor($vendor_id)
    {
        $vendor = app(VendorFactory::class);
        return $vendor->getById($vendor_id);
    }

}