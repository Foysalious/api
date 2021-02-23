<?php


namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Repository\CollectionRepository;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    protected $collectionRepository;

    public function __construct(CollectionRepository $collectionRepository)
    {
        $this->collectionRepository = $collectionRepository;
    }

    public function index(Request $request)
    {
        $collection = $this->collectionRepository->getAllCollection($request->partner);
        return api_response($request, null, 200, $collection);
    }
}