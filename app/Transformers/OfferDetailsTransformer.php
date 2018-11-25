<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 11/25/2018
 * Time: 5:31 PM
 */

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class OfferDetailsTransformer extends TransformerAbstract
{
    public function transform($offer)
    {
        $target_type = strtolower(snake_case(str_replace("App\\Models\\", '', $offer->target_type)));
        return [
            'id' => $offer->id,
            'thumb' => $offer->thumb,
            'banner' => $offer->banner,
            'title' => $offer->title,
            'short_description' => $offer->short_description,
            'target_link' => $offer->target_link,
            'structured_description' => [
                [
                    'title' => 'Offer Details',
                    'contents' => [
                        'It’s very important that you handle carefully.',
                        'Avoid touching any metallic parts.',
                        'In an airtight bag with a moist paper.',
                        'Portant that you handle carefully.',
                        'It’s very important that you handle  moist paper.'
                    ]
                ],
                [
                    'title' => 'Terms & Conditions',
                    'contents' => [
                        'It’s very important that you handle carefully.',
                        'Avoid touching any metallic parts.',
                        'In an airtight bag with a moist paper.',
                        'Portant that you handle carefully.',
                        'It’s very important that you handle  moist paper.'
                    ]
                ]
            ],
            'target_type' => $target_type,
            'amount' => 200,
            'amount_text' => $target_type == 'voucher' ? 'Save Up to' : 'Price',
            'start_date' => $offer->start_date,
            'end_date' => $offer->end_date,
            'is_applied' => false,
        ];

    }
}