<?php

namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Voucher;
use Carbon\Carbon;

class PromotionList
{
    public static function add($customer, $promo)
    {
        $promoList = new PromotionList();
        $voucher = $promoList->isValid($promo, $customer);
        if ($voucher != false) {
            if ($promoList->isAlreadyAdded($voucher, $customer) == false) {
                return $promoList->create($customer, $voucher->id);
            }
        } else {
            return false;
        }
    }

    private function isValid($promo, $customer)
    {
        $timestamp = Carbon::now();
        $voucher = Voucher::where('code', $promo)
            ->where(function ($q) use ($customer) {
                $q->where('owner_id', '<>', $customer)->orWhere('owner_id', null);
            })->where(function ($query) use ($timestamp) {
                $query->where('is_referral', 1)->orWhere([
                    ['start_date', '<=', $timestamp],
                    ['end_date', '>=', $timestamp]
                ]);
            })->first();
        return $voucher != null ? $voucher : false;
    }

    private function isAlreadyAdded($voucher, $customer)
    {
        $customer = Customer::find($customer);
        foreach ($customer->promotions as $promotion) {
            if ($promotion->voucher->id == $voucher->id) {
                return true;
            }
            if ($voucher->is_referral == 1) {
                if ($customer->referrer_id != '') {
                    return true;
                } elseif (count($customer->orders) > 0 || $promotion->voucher->is_referral == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    public function create($customer, $voucher)
    {
        $customer = Customer::find($customer);
        $voucher = Voucher::find($voucher);
        $promo = new Promotion();
        $promo->customer_id = $customer->id;
        $promo->voucher_id = $voucher->id;
        $promo->is_valid = 1;
        $date = Carbon::now()->addDays(90);
        $promo->valid_till = $date->toDateString() . " 23:59:59";
        if ($promo->save()) {
            return Promotion::with(['voucher' => function ($q) {
                $q->select('id', 'code', 'amount');
            }])->select('id', 'voucher_id', 'customer_id', 'valid_till')->where('id', $promo->id)->first();
        }
        return false;
    }
}