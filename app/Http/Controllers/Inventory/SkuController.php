<?php namespace App\Http\Controllers\Inventory;

use App\Sheba\InventoryService\Services\SkuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class SkuController extends Controller
{
    /** @var SkuService */
    private $skuService;

    public function __construct(SkuService $skuService)
    {
        $this->skuService = $skuService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $skus = $this->skuService->setSkuIds($request->skus)->setChannelId($request->channel_id)->getSkus($partner->id);
        return http_response($request, null, 200, $skus);
    }
}
