<?php namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Sheba\Partner\Delivery\Methods;
use App\Sheba\Pos\Partner\PartnerService;
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
        $partner = Partner::where('id', $partner)->select('id', 'name', 'logo', 'sub_domain', 'delivery_charge')->first();
        $partner->delivery_method = $this->getDeliveryMethod($partner->id);
        removeRelationsAndFields($partner, ['webstore_banner']);
        if (!$partner) return http_response($request, null, 404);
        return http_response($request, $partner, 200, ['partner' => $partner]);
    }

    private function getDeliveryMethod($partnerId)
    {
        $partnerDeliveryInformation =  PartnerDeliveryInformation::where('partner_id', $partnerId)->first();
        return (empty($partnerDeliveryInformation) || ($partnerDeliveryInformation->delivery_vendor == Methods::OWN_DELIVERY)) ? Methods::OWN_DELIVERY : Methods::SDELIVERY;
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
