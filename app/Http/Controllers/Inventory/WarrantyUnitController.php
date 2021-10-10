<?php

namespace App\Http\Controllers\Inventory;

use App\Sheba\InventoryService\Services\WarrantyUnitService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WarrantyUnitController extends Controller
{
    protected $warrantyUnitService;

    public function __construct(WarrantyUnitService $warrantyUnitService)
    {
        $this->warrantyUnitService = $warrantyUnitService;
    }

    public function getWarrantyList()
    {
        return $this->warrantyUnitService->getWarrantyUnitList();
    }
}
