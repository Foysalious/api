<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\OfferShowcase;

class CampaignTransformer extends TransformerAbstract
{
    public function transform(OfferShowcase $offer)
    {
        return [
            "target_type" => $offer->type() == "" ? null : $offer->type(),
            "target_id" => (int)$offer->target_id,
            "target_link" => $offer->target_link,
            "title" => $offer->title ?: null,
            "description" => $offer->detail_description ?: null,
            "image" => $offer->app_banner ?: ($offer->app_thumb ?: null),
        ];
    }
}