<?php namespace Sheba\Checkout;

use Illuminate\Database\Eloquent\Collection;

class PartnerSort
{
    /** @var Collection */
    private $partners;
    private $weights;

    public function __construct()
    {
        $this->weights = config('sheba.weight_on_partner_list');
    }

    public function setPartners($partners)
    {
        $this->partners = $partners;
        return $this;
    }

    public function getSortedPartners()
    {
        if (count($this->partners) == 1) return $this->partners;
        $min_orders = $this->partners->min('total_completed_orders');
        $max_orders = $this->partners->max('total_completed_orders');
        $order_difference = $max_orders - $min_orders;

        $min_total_ratings = $this->partners->min('total_ratings');
        $max_total_ratings = $this->partners->max('total_ratings');
        $rating_difference = $max_total_ratings - $min_total_ratings;

        $min_current_impression = $this->partners->min('current_impression');
        $max_current_impression = $this->partners->max('current_impression');
        $current_impression_difference = $max_current_impression - $min_current_impression;

        foreach ($this->partners as $partner) {
            $impression = 0;
            $expert_difference = $partner->subscription->resource_cap - 1;
            $avg_rating = $partner->rating > 0 ? $this->weights['avg_rating'] * (($partner->rating - 1) / 4) : 0;
            $total_ratings = ($partner->total_ratings > 0 && $rating_difference > 0) ? $this->weights['total_ratings'] * (($partner->total_ratings - $min_total_ratings) / $rating_difference) : 0;
            $total_experts = ($partner->total_experts > 0 && $expert_difference > 0) ? $this->weights['capacity'] * (($partner->total_experts - 1) / $expert_difference) : 0;
            $orders = ($partner->total_completed_orders > 0 && $order_difference > 0) ? $this->weights['orders'] * (($partner->total_completed_orders - $min_orders) / $order_difference) : 0;

            if ($current_impression_difference)
                $impression = $partner->current_impression > 10 ?
                    $this->weights['impression'] * (($partner->current_impression - $min_current_impression) / $current_impression_difference) :
                    0;

            $partner['score'] = $avg_rating + $orders + $total_experts + $total_ratings + $impression;
        }
        return $this->partners->sortByDesc('score');
    }

}
