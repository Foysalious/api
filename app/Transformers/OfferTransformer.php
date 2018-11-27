<?php

namespace App\Transformers;


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
            'icon' => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/categories/1/icon_png.png",
            'gradiant' => ['#4286f4', '#f48041'],
            'structured_title' => array(
                array(
                    'value' => 'Save upto',
                    'is_bold' => 0,
                ),
                array(
                    'value' => 'BDT 500',
                    'is_bold' => 1,
                )
            ),
            'is_flash' => $offer->is_flash,
            'is_applied' => 1,
            'promo_code' => 'TESTOFFER'
        ];
    }
}