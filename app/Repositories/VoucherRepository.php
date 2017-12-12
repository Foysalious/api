<?php

namespace App\Repositories;


use App\Models\Customer;
use App\Models\Profile;
use App\Models\Voucher;

class VoucherRepository
{
    public function isValid($voucher, $service, $partner, $location, $customer, $price, $sales_channel)
    {
        return voucher($voucher)
            ->check($service, $partner, intval($location), $customer, $price, $sales_channel)
            ->reveal();
    }

    public function isOwnVoucher($customer, Voucher $voucher)
    {
        $customer = Customer::where('id', (int)$customer)->first();
        if ($customer == null) {
            $profile = Profile::where('mobile', $customer)->first();
            if ($profile != null) {
                $customer = $profile->customer;
            }
        }
        if ($customer != null) {
            if ($this->isOriginalReferral($voucher)) {
                return $customer->id == $voucher->owner_id;
            }
        }
        return false;
    }

    public function isOriginalReferral($voucher)
    {
        return $voucher->is_referral == 1 && $voucher->referred_from == null;
    }
}