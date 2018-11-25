<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class OfferTransformer extends TransformerAbstract
{
    public function transform($offer)
    {
        return [
            'id' => $offer->id,
            'title' => $offer->title,
            'short_description' => $offer->short_description,
            'type' => strtolower(snake_case(str_replace("App\\Models\\", '', $offer->target_type))),
            'type_id' => $offer->target_id,
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
            )
        ];
    }
}