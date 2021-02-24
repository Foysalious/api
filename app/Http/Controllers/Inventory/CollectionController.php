<?php


namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Repository\CollectionRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        if(empty($collection))
            return api_response($request, "No data found!", 500, $collection);
        else
            return api_response($request, null, 200, $collection);
    }
}