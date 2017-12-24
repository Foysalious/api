<?php namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Voucher;

class ReferralCreator
{
    private $rules = array(
        'sales_channels' => array('Web', 'App'),
        'nth_orders' => [1]
    );
    private $referrer;

    public function __construct($model)
    {
        $this->referrer = $model;
    }

    public function create($referred_voucher_id = null)
    {
        $voucher = new Voucher();
        $voucher->code = $this->referrer->generateReferral();
        if ($referred_voucher_id != null) {
            $voucher->referred_from = $referred_voucher_id;
            array_forget($this->rules, 'nth_orders');
            $this->rules += ['customer_ids' => [Voucher::find($referred_voucher_id)->owner_id]];
        }
        return $this->saveVoucher($voucher);
    }

    public function saveVoucher($voucher)
    {
        $voucher->rules = json_encode($this->rules);
        $voucher->title = $this->referrer->identity . " has gifted you " . constants('REFERRAL_GIFT_AMOUNT') . "tk &#128526;";
        $voucher->amount = constants('REFERRAL_GIFT_AMOUNT');
        $voucher->max_order = 1;
        $voucher->sheba_contribution = 100;
        $voucher->owner_type = get_class($this->referrer);
        $voucher->owner_id = $this->referrer->id;
        $voucher->is_referral = 1;
        if ($voucher->save()) {
            return $voucher;
        }
    }

}