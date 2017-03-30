<?php

namespace App\Repositories;


class VoucherRepository
{
    public function isValid($voucher, $service, $partner, $location, $customer, $price, $sales_channel)
    {
        return voucher($voucher)
            ->check($service, $partner, intval($location), $customer, $price, $sales_channel)
            ->reveal();
    }
}