<?php

namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Voucher;
use Carbon\Carbon;

class PromotionList
{
    private $customer;
    private $message = '';
    private $customerReferralAdded = false;
    private $ambassadorReferralAdded = false;

    public function __construct($customer)
    {
        $this->customer = ($customer) instanceof Customer ? $customer : Customer::find($customer);
    }

    public function add($promo)
    {
        $voucher = $this->isValid($promo, $this->customer);
        if ($voucher != false) {
            if ($this->canAdd($voucher, $this->customer)) {
                return array($this->create($voucher->id), 'successful');
            }
        }
        return array(false, $this->message);
    }

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

    private function canAdd(Voucher $voucher, Customer $customer)
    {
        $customer_order_count = $customer->orders->count();
        $customer->load(['promotions' => function ($q) {
            $q->with('voucher');
        }]);
        $promotions = $customer->promotions;
        if ($voucher->is_referral) {
            $this->calculateIsReferralAdded($promotions);
            if ($voucher->ownerIsCustomer()) {
                if ($customer->referrer_id != '') {
                    $this->message = 'Already used a Customer referral code';
                    return false;
                } elseif ($customer_order_count > 0) {
                    $this->message = "Already took first order";
                    return false;
                } elseif ($this->customerReferralAdded) {
                    $this->message = "Already added a Customer referral code";
                    return false;
                }
            } elseif ($voucher->ownerIsAffiliate()) {
                if ($this->ambassadorReferralAdded) {
                    $this->message = "Already added a Ambassador referral code";
                    return false;
                }
            }
        }
        foreach ($promotions as $promotion) {
            if ($this->voucherAlreadyAdded($voucher, $promotion)) {
                return false;
            }
        }
        if ($voucher->max_order != 0) {
            if ($voucher->usage($customer->id) >= $voucher->max_order) {
                return false;
            }
        }
        $rules = json_decode($voucher->rules);
        if (count($rules) > 0) {
            return $this->voucherRuleMatches($rules, $customer, $customer_order_count);
        }
        return true;
    }

    private function calculateIsReferralAdded($promotions)
    {
        foreach ($promotions as $promotion) {
            if ($promotion->voucher->is_referral && $promotion->voucher->referred_from == null) {
                if ($promotion->voucher->ownerIsCustomer()) {
                    $this->customerReferralAdded = true;
                } elseif ($promotion->voucher->ownerIsAffiliate()) {
                    $this->ambassadorReferralAdded = true;
                }
            }
        }
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
                $q->select('id', 'code', 'amount', 'title', 'is_amount_percentage', 'cap');
            }])->select('id', 'voucher_id', 'customer_id', 'valid_till')->where('id', $promo->id)->first();
        }
        return false;
    }

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
                if ($mobile == $customer->profile->mobile) {
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

    private function voucherAlreadyAdded(Voucher $voucher, $promotion)
    {
        if ($promotion->voucher->id == $voucher->id) {
            $this->message = "Voucher is already added!";
            return true;
        } else {
            return false;
        }
    }
}