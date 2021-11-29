<?php namespace App\Transformers\Partner;

use App\Models\Partner;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use League\Fractal\TransformerAbstract;
use Sheba\Sms\AdaReach;
use Sheba\Sms\Infobip;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;

class WebstoreSettingsTransformer extends TransformerAbstract
{
    public function transform($partner)
    {
        $banner_settings = PartnerWebstoreBanner::where('partner_id', $partner->id)->first();

        return [
            'name' => $partner->name,
            'sub_domain' => $partner->sub_domain,
            'url' => $this->getWebStoreURL($partner),
            'has_webstore' => $partner->has_webstore,
            'is_webstore_published' => $partner->is_webstore_published,
            'logo' => $partner->logo,
            'delivery_charge' => $this->getDeliveryCharge($partner),
            'is_inventory_empty' => !$partner->posServices()->count() ? 1 : 0,
            'address' => $partner->address,
            'wallet' => $partner->wallet,
            'single_sms_cost' => 0.30, //TODO: have to remove value
            'is_webstore_sms_active' => $partner->is_webstore_sms_active,
            'banner' => $banner_settings ? [
                'id' => $banner_settings->id,
                'banner_id' => $banner_settings->banner_id,
                'image_link' => $banner_settings->banner->image_link,
                'title' => $banner_settings->title,
                'description' => $banner_settings->description,
                'is_published' => $banner_settings->is_published
            ] : null,
        ];
    }

    public function getDeliveryCharge(Partner $partner)
    {
        if(!$partner->isMigrated(Modules::POS))
            return $partner->delivery_charge;
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        return $orderService->setPartnerId($partner->id)->getPartnerDetails()['partner']['delivery_charge'];
    }

    public function getWebStoreURL(Partner $partner)
    {
        if($partner->isMigrated(Modules::POS)) {
            return config('sheba.new_webstore_url');
        } else {
            return config('sheba.new_webstore_url');
        }
    }
}