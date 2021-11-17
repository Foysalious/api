<?php namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Sheba\Pos\Partner\PartnerService;
use Illuminate\Http\Request;

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
        removeRelationsAndFields($partner, ['webstore_banner']);
        if (!$partner) return http_response($request, null, 404);
        return http_response($request, $partner, 200, ['partner' => $partner]);
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
