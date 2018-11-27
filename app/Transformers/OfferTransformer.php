<?php

namespace App\Transformers;


use App\Models\Category;
use App\Models\OfferShowcase;
use League\Fractal\TransformerAbstract;

class OfferTransformer extends TransformerAbstract
{
    public function transform(OfferShowcase $offer)
    {
        return [
            'id' => $offer->id,
            'title' => $offer->title,
            'short_description' => $offer->short_description,
            'type' => $offer->type(),
            'type_id' => (int)$offer->target_id,
            'start_date' => $offer->start_date,
            'end_date' => $offer->end_date,
            'icon' => $this->icon($offer),
            'gradiant' => ['#4286f4', '#f48041'],
            'structured_title' => $offer->structured_title,
            'is_flash' => $offer->is_flash,
            'is_applied' => $offer->is_applied,
            'promo_code' => $offer->isVoucher() ? $offer->target->code : null
        ];
    }

    private function icon(OfferShowcase $offer)
    {
        if ($offer->isCategory()) return $offer->target->icon_png;
        elseif ($offer->isCategoryGroup()) return $offer->target->icon_png;
        elseif ($offer->isReward()) return "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/percentage.png";
        elseif ($offer->isVoucher()) {
            $target = $offer->target;
            $rules = json_decode($target->rules);
            if (array_key_exists('categories', $rules)) {
                if (count($rules->categories) == 1) {
                    return (Category::find($rules->categories[0]))->icon_png;
                }

            }
        }
        return "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/percentage.png";
    }
}