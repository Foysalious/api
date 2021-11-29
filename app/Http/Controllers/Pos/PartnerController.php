<?php namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Sheba\Partner\Delivery\Methods;
use App\Sheba\Pos\Partner\PartnerService;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Illuminate\Http\Request;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;

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
        $partner = Partner::where('id', $partner)->select('id', 'name', 'logo', 'sub_domain')->first();
        if (!$partner) return http_response($request, null, 404);
        list($is_registered_for_sdelivery,$delivery_method,$delivery_charge) = $this->getDeliveryInformation($partner->id);
        $partner->is_registered_for_sdelivery = $is_registered_for_sdelivery;
        $partner->delivery_method = $delivery_method;
        $partner->delivery_charge = $delivery_charge;
        removeRelationsAndFields($partner, ['webstore_banner']);
        return http_response($request, $partner, 200, ['partner' => $partner]);
    }

    private function getDeliveryInformation($partnerId)
    {
        $partnerDeliveryInformation =  PartnerDeliveryInformation::where('partner_id', $partnerId)->first();
        $is_registered_for_sdelivery = !(empty($partnerDeliveryInformation))  ? 1 : 0;
        $delivery_method = (empty($partnerDeliveryInformation) || ($partnerDeliveryInformation->delivery_vendor == Methods::OWN_DELIVERY)) ? Methods::OWN_DELIVERY : Methods::SDELIVERY;
        $delivery_charge = $delivery_method == Methods::OWN_DELIVERY ? (double)$this->getDeliveryCharge($this->partner) : null;
        return [$is_registered_for_sdelivery,$delivery_method,$delivery_charge];
    }
    public function getDeliveryCharge(Partner $partner)
    {
        if(!$partner->isMigrated(Modules::POS))
            return $partner->delivery_charge;
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        return $orderService->setPartnerId($partner->id)->getPartnerDetails()['partner']['delivery_charge'];
    }
    public function getWebStoreBanner($partner, Request $request)
    {
        $partner = Partner::where('id', $partner)->first();
        $banner = $this->posCustomerService->partnerWebstoreBanner($partner);
        return http_response($request, $partner, 200, ['data' => [$banner]]);

    }

    public function getBanner(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $banner = $this->posCustomerService->partnerWebstoreBanner($partner);
        return http_response($request, $partner, 200, ['data' => [$banner]]);
    }
}
