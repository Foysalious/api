<?php namespace App\Transformers;

use App\Models\Category;
use App\Models\OfferShowcase;
use League\Fractal\TransformerAbstract;
use Sheba\AppSettings\HomePageSetting\DS\Builders\ItemBuilder;

class OfferTransformer extends TransformerAbstract
{
    public function transform(OfferShowcase $offer)
    {
        return [
            'id' => $offer->id,
            'title' => $offer->title,
            'short_description' => $offer->short_description,
            'thumb' => $offer->thumb,
            'app_thumb' => $offer->app_thumb,
            'banner' => $offer->banner,
            'app_banner' => $offer->app_banner,
            'type' => $offer->type(),
            'type_id' => (int)$offer->target_id,
            'target_link' => $offer->target_link ?: null,
            'start_date' => $offer->start_date->toDateTimeString(),
            'end_date' => $offer->end_date->toDateTimeString(),
            'icon' => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/percentage.png",
            'gradient' => config('sheba.gradients')[array_rand(config('sheba.gradients'))],
            'structured_title' => $offer->structured_title,
            'is_flash' => $offer->is_flash,
            'is_applied' => $offer->is_applied,
            'is_campaign' => $offer->is_campaign,
            'promo_code' => $offer->isVoucher() ? $offer->target->code : null,
            'slug' => $offer->isCategory() || $offer->isService() ? $offer->target->getSlug() : null
        ];
    }


    private function link(OfferShowcase $offer)
    {
        $model = $offer->target_type;
        if ($model == 'App\\Models\\ExternalProject') {
            $model = $model::find((int)$offer->target_id);
            $item_builder = (new ItemBuilder())->buildExternalProject($model);
            return $item_builder;
        } else {
            return null;
        }
    }

    private function icon(OfferShowcase $offer)
    {
        if ($offer->isCategory()) return $offer->target->icon_png;
        elseif ($offer->isCategoryGroup()) return $offer->target->icon_png;
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