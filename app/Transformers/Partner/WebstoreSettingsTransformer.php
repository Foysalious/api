<?php namespace App\Transformers\Partner;


use League\Fractal\TransformerAbstract;

class WebstoreSettingsTransformer extends TransformerAbstract
{
    public function transform($partner)
    {
        return [
            'name' => $partner->name,
            'sub_domain' => $partner->sub_domain,
            'has_webstore' => $partner->has_webstore,
            'is_webstore_published' => $partner->is_webstore_published,
            'logo' => $partner->logo,
            'delivery_charge' => $partner->delivery_charge,
            'is_inventory_empty' => !$partner->posServices()->count() ? 1 : 0
        ];
    }
}