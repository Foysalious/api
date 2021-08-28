<?php namespace App\Http\Controllers\PosRebuild;

use App\Exceptions\NotFoundException;
use App\Models\Partner;
use App\Sheba\PosRebuild\AccessManager\AccessManager;
use App\Sheba\PosRebuild\AccessManager\Features;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccessManagerController extends Controller
{
    /**
     * @var AccessManager
     */
    private $accessManager;

    public function __construct(AccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * @throws NotFoundException
     */
    public function checkAccess(Request $request): JsonResponse
    {
        $this->validate($request, [
            'feature' => 'required|in:'. implode(',', Features::get()),
            'partner_id' => 'required|int',
            'product_published_count' => 'required_if:feature,product_webstore_publish|int'
        ]);
        $partner = Partner::find($request->partner_id);
        if (!$partner) throw new NotFoundException('Partner not found.', 404);
        $accessManager = $this->accessManager->setFeature($request->feature)->setAccessRules($partner->subscription->getAccessRules());
        if ($request->has('product_published_count')) $accessManager = $accessManager->setProductPublishedCount($request->product_published_count);
        $canAccess = $accessManager->checkAccess();
        if ($canAccess) return http_response($request, null, 200, ["message" => "Successful"]);
        return http_response($request, null, 403, ["message" => "Your package doesn't have access to this feature please upgrade"]);


    }
}
