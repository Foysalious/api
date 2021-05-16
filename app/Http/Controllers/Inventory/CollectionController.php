<?php


namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Repository\CollectionRepository;
use App\Sheba\InventoryService\Services\CollectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    protected $collectionRepository;

    Private $collectionService;

    public function __construct(CollectionRepository $collectionRepository, CollectionService $collectionService)
    {
        $this->collectionRepository = $collectionRepository;
        $this->collectionService = $collectionService;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $collection = $this->collectionService->setPartnerId($partner->id)->setOffset($request->offset)->setLimit($request->limit)->getAllCollection();
        if(empty($collection)) return api_response($request, "No data found!", 500, $collection);
        else return api_response($request, null, 200, $collection);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->collectionService
            ->setPartnerId($partner->id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setThumb($request->thumb)
            ->setBanner($request->banner)
            ->setAppThumb($request->app_thumb)
            ->setAppBanner($request->app_banner)
            ->setIsPublished($request->is_published)
            ->setProducts($request->products)
            ->store();
        return http_response($request, null, 201, $response);
    }

    public function show(Request $request, $collection_id)
    {
        $partner = $request->auth_user->getPartner();
        try {
            $collection = $this->collectionService->setPartnerId($partner->id)->setCollectionId($collection_id)->getDetails();
        } catch(\Exception $exception) {
            return http_response($request, 'No Data found!', 500, null);
        }
        return http_response($request, null, 200, $collection);
    }


    public function update(Request $request, $collection_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->collectionService
            ->setPartnerId($partner->id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setThumb($request->thumb)
            ->setBanner($request->banner)
            ->setAppThumb($request->app_thumb)
            ->setAppBanner($request->app_banner)
            ->setIsPublished($request->is_published)
            ->setCollectionId($collection_id)
            ->setProducts($request->products)
            ->update();
        return http_response($request, null, 200, $response);
    }

    public function destroy(Request $request, $collection_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->collectionService->setPartnerId($partner->id)->setCollectionId($collection_id)->delete();
        return http_response($request, null, 200, $response);
    }
}