<?php namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Voucher;

class ReferralCreator
{
    const AMOUNT = 200;

    public static function create(Customer $customer)
    {
        $voucher = new Voucher();
        $voucher->code = $customer->generateReferral();
        $referral = new ReferralCreator();
        $referral->saveVoucher($customer, $voucher);
    }

    public function saveVoucher($customer, $voucher)
    {
        $voucher->rules = json_encode(array(
            'sales_channels' => array('Web', 'App'),
            'nth_orders' => [1]
        ));
        $voucher->title = $this->getIdentity($customer) . " has gifted you " . self::AMOUNT . "tk &#128526;";
        $voucher->amount = self::AMOUNT;
        $voucher->max_order = 1;
        $voucher->sheba_contribution = 100;
        $voucher->owner_type = 'App\Models\Customer';
        $voucher->owner_id = $customer->id;
        $voucher->start_date = $customer->id;
        $voucher->is_referral = 1;
        $voucher->save();
    }

    private function getIdentity($customer)
    {
        if ($customer->name != '') {
            return $customer->name;
        } elseif ($customer->mobile) {
            return $customer->mobile;
        }
        return $customer->email;
    }
}