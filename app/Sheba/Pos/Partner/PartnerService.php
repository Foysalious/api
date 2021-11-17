<?php namespace App\Sheba\Pos\Partner;

class PartnerService
{
    public function partnerWebstoreBanner($partner)
    {
        $web_store_banner = $partner->webstoreBanner;
        if (!$web_store_banner) return null;
        return [
            'id' => $web_store_banner->id,
            'banner_id' => $web_store_banner->banner_id,
            'image_link' => $web_store_banner->banner->image_link,
            'title' => $web_store_banner->title,
            'description' => $web_store_banner->description,
            'is_published' => $web_store_banner->is_published
        ];
    }
}
