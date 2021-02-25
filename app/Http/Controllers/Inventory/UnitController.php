<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\UnitService;
use Illuminate\Http\Request;


use GuzzleHttp\Client;

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
        return api_response($request, null, 200, $units);

    }
}


