<?php namespace App\Transformers\Partner;


use League\Fractal\TransformerAbstract;

class WebstoreBannerTransformer extends TransformerAbstract
{
    public function transform($partnerBanner)
    {
        return [
            'id' => $partnerBanner->id,
            'banner_id' => $partnerBanner->banner_id,
            'image_link' => $partnerBanner->banner->image_link,
            'title' => $partnerBanner->title,
            'description' => $partnerBanner->description,
            'is_published' => $partnerBanner->is_published
        ];
    }
}