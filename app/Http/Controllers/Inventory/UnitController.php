<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\UnitService;
use Illuminate\Http\Request;


use GuzzleHttp\Client;

class UnitController extends Controller
{

    private $unitRepository;

    public function __construct(UnitService $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function index(Request $request)
    {

        $units = $this->unitRepository->getallunits();
        return api_response($request, null, 200, $units);

    }
}


