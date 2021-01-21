<?php namespace App\Transformers\Partner;


use League\Fractal\TransformerAbstract;
use Sheba\Sms\Infobip;

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
            'is_inventory_empty' => !$partner->posServices()->count() ? 1 : 0,
            'address' => $partner->address,
            'wallet' => $partner->wallet,
            'single_sms_cost' => Infobip::SINGLE_SMS_COST,
            'is_webstore_sms_active' => $partner->is_sms_active
        ];
    }
}