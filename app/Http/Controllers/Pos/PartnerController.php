<?php namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Sheba\Partner\Delivery\Methods;
use App\Sheba\Pos\Partner\PartnerService;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\WebstoreBannerTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;

class PartnerController extends Controller
{
    /**
     * @var PartnerService
     */
    private $posCustomerService;

    public function __construct(PartnerService $posCustomerService)
    {
        $this->posCustomerService = $posCustomerService;
    }

    public function findById($partner, Request $request)
    {
        /** delivery_charge is used only in pos-order */
        $partner = Partner::where('id', $partner)->select('id', 'name', 'logo', 'sub_domain', 'delivery_charge', 'is_webstore_sms_active')->with(['posSetting', 'webstoreDomain'])->first();
        if (!$partner) return http_response($request, null, 404);
        list($is_registered_for_sdelivery, $delivery_method, $delivery_charge) = $this->getDeliveryInformation($partner);
        $partner->is_registered_for_sdelivery = $is_registered_for_sdelivery;
        $partner->delivery_method = $delivery_method;
        $partner->delivery_charge = $delivery_charge;
        $partner->vat_percentage = $partner->posSetting ? $partner->posSetting->vat_percentage : 0.0;
        $partner->own_domain = $partner->webstoreDomain ? $partner->webstoreDomain->domain_name : null;
        removeRelationsAndFields($partner, ['webstore_banner']);
        return http_response($request, $partner, 200, ['partner' => $partner]);
    }

    private function getDeliveryInformation($partner)
    {
        $partnerDeliveryInformation = PartnerDeliveryInformation::where('partner_id', $partner->id)->first();
        $is_registered_for_sdelivery = !(empty($partnerDeliveryInformation)) ? 1 : 0;
        $delivery_method = (empty($partnerDeliveryInformation) || ($partnerDeliveryInformation->delivery_vendor == Methods::OWN_DELIVERY)) ? Methods::OWN_DELIVERY : Methods::SDELIVERY;
        $delivery_charge = $delivery_method == Methods::OWN_DELIVERY ? (double)$partner->delivery_charge : null;
        return [$is_registered_for_sdelivery, $delivery_method, $delivery_charge];
    }

    public function getDeliveryCharge(Partner $partner)
    {
        if (!$partner->isMigrated(Modules::POS))
            return $partner->delivery_charge;
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        return $orderService->setPartnerId($partner->id)->getPartnerDetails()['partner']['delivery_charge'];
    }

    public function getWebStoreBanner($partner, Request $request)
    {
        $partnerBanners = PartnerWebstoreBanner::where('partner_id', $partner)->where('is_published', 1)->get();
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Collection($partnerBanners, new WebstoreBannerTransformer());
        $banners = $fractal->createData($resource)->toArray()['data'];
        if (empty($banners)) return http_response($request, null, 404);
        return http_response($request, $resource, 200, ['data' => $banners]);
    }

    public function getBanner(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $banner = $this->posCustomerService->partnerWebstoreBanner($partner);
        return http_response($request, $partner, 200, ['data' => [$banner]]);
    }
}
