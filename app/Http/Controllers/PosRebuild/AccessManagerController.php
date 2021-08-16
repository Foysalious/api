<?php namespace App\Http\Controllers\PosRebuild;

use App\Exceptions\NotFoundException;
use App\Models\Partner;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Sheba\Subscription\Partner\Access\AccessManager;
use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;

class AccessManagerController extends Controller
{
    /**
     * @throws AccessRestrictedExceptionForPackage
     * @throws NotFoundException
     */
    public function checkAccess(Request $request)
    {
        $this->validate($request, [
            'feature' => 'required|string',
            'partner_id' => 'required|int',
            'product_published_count' => 'required_if:feature,pos.ecom.product_publish|int'
        ]);
        $partner = Partner::find($request->partner_id);
        if (!$partner) throw new NotFoundException('Partner not found.', 404);
        if ($request->feature == AccessManager::Rules()->POS->ECOM->PRODUCT_PUBLISH
            && $request->product_published_count < $partner->subscription->getAccessRules()['pos']['ecom']['product_publish_limit']) {
            return http_response($request, null, 200);
        }
        try {
            AccessManager::checkAccess($request->feature, $partner->subscription->getAccessRules());
        } catch (\Exception $exception) {
            return http_response($request, null, 200, ["code" => 403, "message" => "Your package doesn't have access to this feature please upgrade"]);
        }
        return http_response($request, null, 200, ["code" => 200, "message" => "Successful"]);

    }
}
