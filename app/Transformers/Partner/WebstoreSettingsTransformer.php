<?php namespace App\Transformers\Partner;

use App\Models\Partner;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;
use Sheba\Sms\AdaReach;
use Sheba\Sms\Infobip;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;

class WebstoreSettingsTransformer extends TransformerAbstract
{
    /** @todo need to delete the banner property after january release 100% rollout
     * @param $partner
     * @return array
     */
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
            'is_inventory_empty' => $this->isInventoryEmpty($partner),
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

    public function isInventoryEmpty($partner)
    {
        if ($partner->isMigrated(Modules::POS)) {
            $inventoryServerClient = App::make(InventoryServerClient::class);
            $statistics = $inventoryServerClient->get('api/v1/partners/'.$partner->id.'/statistics');
            $total_products = $statistics['statistics']['total_products'] ?? 0;
            return $total_products > 0 ? 0 : 1;
        } else {
            return !$partner->posServices()->count() ? 1 : 0;
        }
    }
}