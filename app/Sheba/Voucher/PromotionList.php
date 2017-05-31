<?php

namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Voucher;
use Carbon\Carbon;

class PromotionList
{
    private $customer;

    public function __construct($customer)
    {
        $this->customer = ($customer) instanceof Customer ? $customer : Customer::find($customer);
    }

    public function add($promo)
    {
        $voucher = $this->isValid($promo, $this->customer);
        if ($voucher != false) {
            if ($this->canAdd($voucher, $this->customer)) {
                return $this->create($voucher->id);
            }
        } else {
            return false;
        }
    }

    /**
     * voucher code exists, code isn't your own promo, check validity of code or check is_referral(because referral validity won't match)
     * @param $promo
     * @param $customer
     * @return bool
     */
    private function isValid($promo, Customer $customer)
    {
        $timestamp = Carbon::now();
        $voucher = Voucher::where('code', $promo)
            ->where(function ($q) use ($customer) {
                $q->where('owner_id', '<>', $customer->id)->orWhere('owner_id', null);
            })
            ->where(function ($query) use ($timestamp) {
                $query->where('is_referral', 1)
                    ->orWhere([
                        ['start_date', '<=', $timestamp],
                        ['end_date', '>=', $timestamp]
                    ]);
            })->first();
        return $voucher != null ? $voucher : false;
    }

    /**
     * @param Voucher $voucher
     * @param Customer $customer
     * @return bool
     */
    private function canAdd(Voucher $voucher, Customer $customer)
    {
        $customer_order_count = $customer->orders->count();
        $promotions = $customer->promotions;
        foreach ($promotions as $promotion) {
            //voucher already added
            if ($promotion->voucher->id == $voucher->id) {
                return false;
            }
            if ($voucher->is_referral) {
                //customer referred id exist, already given first order & already a referral code of someone exists
                if ($customer->referrer_id != '' || $customer_order_count > 0 || ($promotion->voucher->is_referral && $promotion->voucher->referred_from == null)) {
                    return false;
                }
            }
        }
        if ($voucher->usage($customer->id) >= $voucher->max_order) {
            return false;
        }
        $rules = json_decode($voucher->rules);
        if (count($rules) > 0) {
            return $this->voucherRuleMatches($rules, $customer, $customer_order_count);
        }
        return true;
    }

    public function create($voucher)
    {
        $voucher = Voucher::find($voucher);
        $promo = new Promotion();
        $promo->customer_id = $this->customer->id;
        $promo->voucher_id = $voucher->id;
        $promo->is_valid = 1;
        $date = Carbon::now()->addDays(90);
        $promo->valid_till = $voucher->is_referral ? $date->toDateString() . " 23:59:59" : $voucher->end_date;
        if ($promo->save()) {
            return Promotion::with(['voucher' => function ($q) {
                $q->select('id', 'code', 'amount', 'title');
            }])->select('id', 'voucher_id', 'customer_id', 'valid_till')->where('id', $promo->id)->first();
        }
        return false;
    }

    /**
     * @param $rules
     * @param Customer $customer
     * @param $customer_order_count
     * @return bool
     * @internal param Voucher $voucher
     * @internal param $order_count
     */
    private function voucherRuleMatches($rules, Customer $customer, $customer_order_count)
    {
        if (array_key_exists('nth_orders', $rules)) {
            $nth_orders = $rules->nth_orders;
            //customer order is less than max nth order value
            if ($customer_order_count >= max($nth_orders)) {
                return false;
            }
        }
        if (array_key_exists('customers', $rules)) {
            $for_you = false;
            $mobiles = $rules->customers;
            foreach ($mobiles as $mobile) {
                if ($mobile == $customer->mobile) {
                    $for_you = true;
                }
            }
            //voucher is not for you
            if ($for_you == false) {
                return false;
            }
        }
        if (array_key_exists('customer_ids', $rules)) {
            $for_you = false;
            $customer_ids = $rules->customer_ids;
            foreach ($customer_ids as $customer_id) {
                if ($customer_id == $customer->id) {
                    $for_you = true;
                }
            }
            //voucher is not for you
            if ($for_you == false) {
                return false;
            }
        }
        return true;
    }
}