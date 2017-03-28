<?php

namespace App\Repositories;


class VoucherRepository
{
    public function isValid($voucher, $service, $partner, $location, $customer, $price)
    {
        return voucher($voucher)
            ->check($service, $partner, intval($location), intval($customer), $price)
            ->reveal();
    }
}