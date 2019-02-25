<?php namespace Sheba\MovieTicket;


use Sheba\MovieTicket\Vendor\BlockBuster;
use Sheba\MovieTicket\Vendor\Vendor;
use Sheba\MovieTicket\Vendor\VendorManager;

class MovieTicket
{
    /**
     * @var VendorManager $vendorManager
     */
    private $vendorManager;

    public function __construct(VendorManager $vendorManager)
    {
       $this->vendorManager = $vendorManager;
    }

    public function initVendor() {
        $this->vendorManager->setVendor(new BlockBuster('dev'))->initVendor();
        return $this;
    }

    public function getAvailableTickets() {
        return $this->vendorManager->get();
    }

}