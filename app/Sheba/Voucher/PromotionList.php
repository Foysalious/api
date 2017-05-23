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
            if ($this->isAlreadyAdded($voucher, $this->customer) == false) {
                return $this->create($this->customer, $voucher->id);
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
    private function isAlreadyAdded(Voucher $voucher, Customer $customer)
    {
        $promotions = $customer->promotions;
        foreach ($promotions as $promotion) {
            if ($promotion->voucher->id == $voucher->id) {
                return true;
            }
        }
        return false;
    }

    private function isVoucherAddable(Voucher $voucher, Customer $customer)
    {
        //If voucher is referral
        if ($voucher->is_referral == 1) {
            if ($customer->referrer_id != '') {
                return true;
            } elseif (count($customer->orders) > 0 || $promotion->voucher->is_referral == 1) {
                return true;
            }
        }
    }


    public function create(Customer $customer, $voucher)
    {
        $voucher = Voucher::find($voucher);
        $promo = new Promotion();
        $promo->customer_id = $customer->id;
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
}