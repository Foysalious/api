<?php namespace Sheba\Offer;

use Sheba\Dal\Category\Category;
use App\Models\Customer;
use App\Models\Location;
use App\Models\OfferShowcase;
use App\Models\Voucher;
use Sheba\Voucher\DTO\Params\CheckParamsForPromotion;
use Sheba\Voucher\VoucherRuleChecker;

class OfferFilter
{
    private $offers;
    private $customer;
    private $category;
    private $location;

    public function __construct($offers)
    {
        $this->offers = $offers;
    }

    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;
    }

    public function setCategory(Category $category = null)
    {
        $this->category = $category;
    }

    public function setLocation(Location $location = null)
    {
        $this->location = $location;
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
                    $voucher_rule = new VoucherRuleChecker($offer->target->rules);
                    $is_applicable = 0;
                    $categories = Category::whereIn('id', $category_ids)->with('parent')->get();
                    foreach ($categories as $category) {
                        $is_applicable = $voucher_rule->checkCategory($category->id) && $voucher_rule->checkMasterCategory($category->parent->id);
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
                    if ($reward->noConstraints()->where('constraint_type', "Sheba\\Dal\\Category\\Category")->first()) continue;
                    $ids = $reward->constraints()->where('constraint_type', "Sheba\\Dal\\Category\\Category")->pluck('constraint_id')->toArray();
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
            if ($this->location) {
                $locations = $offer->locations->pluck('id')->toArray();
                if (!in_array($this->location->id, $locations))
                    unset($this->offers[$key]);
            }
        }
        return $this->offers;
    }

    private function isApplicableVoucher(Voucher $voucher)
    {
        $rules = json_decode($voucher->rules, 1);
        if (count($rules) == 0) return true;
        if ($voucher->max_order > 0 && $this->customer->orders->where('voucher_id', $voucher->id)->count() >= $voucher->max_order) return false;
        $params = (new CheckParamsForPromotion())->setApplicant($this->customer);
        if (voucher($voucher)->checkForPromotion($params)->isInValid()) {
            return false;
        }
        if (array_key_exists('nth_orders', $rules)) {
            if ($this->customer->orders->count() >= max($rules['nth_orders'])) return false;
        }
        return true;
    }
}