<?php namespace App\Http\Controllers\Inventory;

use App\Sheba\InventoryService\Services\ValueService;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ValueController extends Controller
{
    protected $valueService;

    /**
     * ValueController constructor.
     * @param $valueService
     */
    public function __construct(ValueService $valueService)
    {
        $this->valueService = $valueService;
    }

    public function store(Request $request, $partnerId, $optionId)
    {
        $partner = $request->auth_user->getPartner();
        $value = $this->valueService->setOptionId($optionId)->setPartnerId($partner->id)->setName($request->name)->store();
        return http_response($request, null, 200, $value);
    }

    public function update($partnerId, $valueId, Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $value = $this->valueService->setValueId($valueId)->setPartnerId($partner->id)->setName($request->name)->update();
        return http_response($request, null, 200, $value);
    }

    public function destroy(Request $request, $valueId)
    {
        $value = $this->valueService
            ->setValueId($valueId)
            ->delete();
        return http_response($request, null, 200, $value);
    }
}
