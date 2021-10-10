<?php namespace App\Http\Controllers\Inventory;

use App\Sheba\InventoryService\Services\OptionService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class OptionController extends Controller
{
    protected $optionService;

    /**
     * OptionController constructor.
     * @param OptionService $optionService
     */
    public function __construct(OptionService $optionService)
    {
        $this->optionService = $optionService;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $options = $this->optionService->setPartnerId($partner->id)->getOptions();
        return http_response($request, null, 200, $options);
    }

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $option = $this->optionService->setPartnerId($partner->id)->setName($request->name)->store();
        return http_response($request, null, 200, $option);
    }

    public function update($optionId, Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $option = $this->optionService->setOptionId($optionId)->setPartnerId($partner->id)->setName($request->name)->update();
        return http_response($request, null, 200, $option);
    }

    public function destroy(Request $request, $optionId)
    {

        $option = $this->optionService
            ->setOptionId($optionId)
            ->delete();
        return http_response($request, null, 200, $option);
    }
}
