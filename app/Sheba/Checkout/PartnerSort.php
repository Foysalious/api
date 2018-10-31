<?php

namespace Sheba\Checkout;


class PartnerSort
{
    private $partners;
    private $goldPartners;
    private $goldPartnerCount;
    private $silverPartners;
    private $silverPartnerCount;
    private $bronzePartners;
    private $bronzePartnerCount;
    private $sortedPartners;
    private $weights;
    private $shebaHelpDesk;

    public function __construct($partners)
    {
        $this->sortedPartners = collect();
        $this->partners = $partners;
        $this->filterPartnersByPackage();
        $this->weights = config('sheba.weight_on_partner_list');
        $this->goldPartnerCount = config('sheba.partner_packages_on_partner_list')['ESP'];
        $this->silverPartnerCount = config('sheba.partner_packages_on_partner_list')['PSP'];
        $this->bronzePartnerCount = config('sheba.partner_packages_on_partner_list')['LSP'];
        $this->setPartners();

    }

    private function setPartners()
    {
        $remaining = 0;
        if ($this->goldPartners->count() < $this->goldPartnerCount) {
            $remaining = $this->goldPartnerCount - $this->goldPartners->count();
            $this->goldPartnerCount = $this->goldPartners->count();
            $this->silverPartnerCount += $remaining;
        }
        if ($this->silverPartners->count() < $this->silverPartnerCount) {
            $remaining = $this->silverPartnerCount - $this->silverPartners->count();
            $this->silverPartnerCount = $this->silverPartners->count();
            $this->bronzePartnerCount += $remaining;
        }
        if ($this->bronzePartners->count() < $this->bronzePartnerCount) {
            $this->bronzePartnerCount = $this->bronzePartners->count();
        }
    }

    public function get()
    {
        $this->goldPartners = $this->calculateTotalWeight($this->goldPartners)->splice(0, $this->goldPartnerCount);
        $this->silverPartners = $this->calculateTotalWeight($this->silverPartners)->splice(0, $this->silverPartnerCount);
        $this->bronzePartners = $this->calculateTotalWeight($this->bronzePartners)->splice(0, $this->bronzePartnerCount);
        $this->sortedPartners = $this->sortedPartners->merge($this->goldPartners)->merge($this->silverPartners)->merge($this->bronzePartners);
        return $this->sortedPartners->count() > 0 ? $this->sortedPartners : $this->shebaHelpDesk;
    }

    private function calculateTotalWeight($partners)
    {
        $min_total_experts = $partners->min('total_experts');
        $max_total_experts = $partners->max('total_experts');
        $expert_difference = $max_total_experts - $min_total_experts;

        $min_orders = $partners->min('total_completed_orders');
        $max_orders = $partners->max('total_completed_orders');
        $order_difference = $max_orders - $min_orders;

        $min_price = $partners->min('discounted_price');
        $max_price = $partners->max('discounted_price');
        $price_difference = $max_price - $min_price;

        $min_total_ratings = $partners->min('total_ratings');
        $max_total_ratings = $partners->max('total_ratings');
        $rating_difference = $max_total_ratings - $min_total_ratings;

        $min_current_impression = 10;
        $max_current_impression = 1000;
        $current_impression_difference = $max_current_impression - $min_current_impression;

        foreach ($partners as $partner) {
            $avg_rating = $partner->rating > 0 ? $this->weights['avg_rating'] * (($partner->rating - 1) / 4) : 0;
            $total_ratings = ($partner->total_ratings > 0 && $rating_difference > 0) ? $this->weights['total_ratings'] * (($partner->total_ratings - $min_total_ratings) / $rating_difference) : 0;
            $total_experts = ($partner->total_experts > 0 && $expert_difference > 0) ? $this->weights['capacity'] * (($partner->total_experts - $min_total_experts) / $expert_difference) : 0;
            $orders = ($partner->total_completed_orders > 0 && $order_difference > 0) ? $this->weights['orders'] * (($partner->total_completed_orders - $min_orders) / $order_difference) : 0;
            $impression = $partner->current_impression > 10 ? $this->weights['impression'] * (($partner->current_impression - $min_current_impression) / $current_impression_difference) : 0;
            $price = 1 - (($price_difference > 0) ? ($this->weights['price'] * (($partner->discounted_price - $min_price) / $price_difference)) : 0);
            $partner['score'] = $price + $avg_rating + $orders + $total_experts + $total_ratings + $impression;
        }
        return $partners->sortByDesc('score');
    }

    private function filterPartnersByPackage()
    {
        $group_by_packages = $this->partners->groupBy('package_id');
        $this->goldPartners = isset($group_by_packages[config('sheba.partner_packages')['ESP']]) ? $group_by_packages[config('sheba.partner_packages')['ESP']] : collect();;
        $this->silverPartners = isset($group_by_packages[config('sheba.partner_packages')['PSP']]) ? $group_by_packages[config('sheba.partner_packages')['PSP']] : collect();
        $this->bronzePartners = isset($group_by_packages[config('sheba.partner_packages')['LSP']]) ? $group_by_packages[config('sheba.partner_packages')['LSP']] : collect();
        $this->shebaHelpDesk = $this->partners->where('id', 1809);
        $this->bronzePartners = $this->bronzePartners->reject(function ($partner) {
            return $partner->id == 1809;
        });
    }

}