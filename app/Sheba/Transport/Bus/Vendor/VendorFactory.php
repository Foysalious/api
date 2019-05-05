<?php namespace Sheba\Transport\Bus\Vendor;

use App\Models\Transport\TransportTicketVendor;
use Exception;
use ReflectionClass;
use Sheba\MovieTicket\Vendor\BlockBuster\BlockBuster;

class VendorFactory
{
    private $classes = [
        BlockBuster::class
    ];

    /**
     * @param $id
     * @return Vendor
     * @throws Exception
     */
    public function getById($id)
    {
        if (!in_array($id, $this->getConstants())) {
            throw new Exception('Invalid Vendor');
        }
        return app($this->classes[$id - 1])->setModel($this->getModel($id));
    }

    public function getModel($id)
    {
        return TransportTicketVendor::find($id);
    }

    private function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}