<?php namespace Sheba\Transport\Bus\Vendor;

use App\Models\Transport\TransportTicketVendor;

abstract class Vendor
{
    protected $model;

    public function setModel(TransportTicketVendor $model)
    {
        $this->model = $model;
        return $this;
    }

    abstract function bookTicket();
}