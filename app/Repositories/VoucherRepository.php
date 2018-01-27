<?php

namespace App\Repositories;


use App\Models\Customer;
use App\Models\Profile;
use App\Models\Voucher;

class VoucherRepository
{
    /**
     * @param $voucher
     * @param $service
     * @param $partner
     * @param $location
     * @param $customer
     * @param $price
     * @param $sales_channel
     * @return array
     * @throws \Exception
     */
    public function isValid($voucher, $service, $partner, $location, $customer, $price, $sales_channel)
    {
        return voucher($voucher)
            ->check($service, $partner, intval($location), $customer, $price, $sales_channel)
            ->reveal();
    }

    public function isOwnVoucher($customer, Voucher $voucher)
    {
        $customer = $this->getCustomer($customer);
        if ($customer != null) {
            if ($this->isOriginalReferral($voucher)) {
                $owner = $voucher->owner;
                $class_name = class_basename($owner);
                if ($class_name == 'Affiliate' || $class_name == 'Customer') {
                    return $customer->profile->id == $owner->profile->id;
                }
            }
        }
        return false;
    }

    public function isOriginalReferral($voucher)
    {
        return $voucher->is_referral == 1 && $voucher->referred_from == null;
    }

    private function getCustomer($customer)
    {
        $customer = Customer::find((int)$customer);
        if ($customer == null) {
            $profile = Profile::where('mobile', $customer)->first();
            if ($profile != null) {
                $customer = $profile->customer;
            }
        }
        return $customer;
    }

}