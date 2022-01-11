<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;



class UnitController extends Controller
{

    private $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function index(Request $request)
    {
        $units = $this->unitService->getallunits();
        return http_response($request, null, 200, $units);
    }

    public function weightUnits(Request $request): JsonResponse
    {
        $weight_units = $this->unitService->getWeightUnits();
        return http_response($request, null, 200, $weight_units);
    }
}


