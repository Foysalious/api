<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Repository\UnitRepository;
use Illuminate\Http\Request;


use GuzzleHttp\Client;

class UnitController extends Controller
{

    Private $unitRepository;
    public function __construct(UnitRepository $unit_repo)
    {
        $this->unitRepository = $unit_repo;
    }
    public function index(Request $request)
    {

        $units = $this->unitRepository->getallunits();
        return api_response($request, null, 200, $units);

    }

}


