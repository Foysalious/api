<?php

namespace Sheba\Offer;

use App\Models\Category;
use App\Models\Customer;
use App\Models\OfferShowcase;
use App\Models\Voucher;
use Sheba\Voucher\VoucherRule;

class OfferFilter
{
    private $offers;
    private $customer;
    private $category;

    public function __construct($offers)
    {
        $this->offers = $offers;
    }

    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function filter()
    {
        foreach ($this->offers as $key => &$offer) {
            array_add($offer, 'is_applied', 0);
            /** @var OfferShowcase $offer */
            if ($this->customer) {
                if ($offer->isVoucher()) {
                    if (!$this->isApplicableVoucher($offer->target)) {
                        unset($this->offers[$key]);
                        continue;
                    } else {
                        $offer['is_applied'] = $this->customer->promotions->where('voucher_id', $offer->target->id)->first() ? 1 : 0;
                    }
                } elseif ($offer->isReward() && !$offer->target->isCustomer()) {
                    unset($this->offers[$key]);
                    continue;
                }
            }
            if ($this->category) {
                $category_ids = !$this->category->isParent() ? [$this->category->id] : $this->category->children->pluck('id')->toArray();
                if ($offer->isVoucher()) {
                    $voucher_rule = new VoucherRule($offer->target->rules);
                    $is_applicable = 0;
                    foreach ($category_ids as $id) {
                        if ($voucher_rule->checkCategory($id)) $is_applicable = 1;
                    }
                    if (!$is_applicable) {
                        unset($this->offers[$key]);
                        continue;
                    }
                } elseif ($offer->isCategory()) {
                    $offer_category = $offer->target;
                    $ids = !$offer_category->isParent() ? [$offer_category->id] : $offer_category->children->pluck('id')->toArray();
                    $is_applicable = 0;
                    foreach ($category_ids as $id) {
                        if (in_array($id, $ids)) $is_applicable = 1;
                    }
                    if (!$is_applicable) {
                        unset($this->offers[$key]);
                        continue;
                    }
                } elseif ($offer->isCategoryGroup()) {
                    $ids = $offer->target->categories->pluck('id')->toArray();
                    $is_applicable = 0;
                    foreach ($category_ids as $id) {
                        if (in_array($id, $ids)) $is_applicable = 1;
                    }
                    if (!$is_applicable) {
                        unset($this->offers[$key]);
                        continue;
                    }
                } elseif ($offer->isReward()) {
                    $reward = $offer->target;
                    if ($reward->noConstraints()->where('constraint_type', "App\\Models\\Category")->first()) continue;
                    $ids = $reward->constraints()->where('constraint_type', "App\\Models\\Category")->pluck('constraint_id')->toArray();
                    if (count($ids) == 0) continue;
                    $is_applicable = 0;
                    foreach ($category_ids as $id) {
                        if (in_array($id, $ids)) $is_applicable = 1;
                    }
                    if (!$is_applicable) {
                        unset($this->offers[$key]);
                        continue;
                    }
                }
            }
        }
        return $this->offers;
    }

    private function isApplicableVoucher(Voucher $voucher)
    {
        $rules = json_decode($voucher->rules);
        if (count($rules) == 0) return true;
        if ($voucher->max_order > 0 && $this->customer->orders->where('voucher_id', $voucher->id)->count() >= $voucher->max_order) return false;
        $voucher_rule = new VoucherRule($voucher->rules);
        $mobile = $this->customer->profile->mobile;
        if (!$voucher_rule->checkCustomerMobileExcluded($mobile) || !$voucher_rule->checkNumberSeriesExcluded($mobile) ||
            !$voucher_rule->checkCustomerMobile($mobile) || !$voucher_rule->checkCustomerId($this->customer->id) ||
            !$voucher_rule->checkDomainAgainstCustomer($this->customer) || !$voucher_rule->checkDomainExcludedAgainstCustomer($this->customer)) {
            return false;
        }
        if (array_key_exists('nth_orders', $rules)) {
            $nth_orders = $rules->nth_orders;
            if ($this->customer->orders->count() >= max($nth_orders)) return false;
        }
        return true;
    }
}