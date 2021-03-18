<?php


namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\CollectionRequest;
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
        $collection = $this->collectionService->setPartnerId($partner->id)->getAllCollection();
        if(empty($collection))
            return api_response($request, "No data found!", 500, $collection);
        else
            return api_response($request, null, 200, $collection);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */

    public function store(CollectionRequest $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->collectionService
            ->setPartnerId($partner->id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setThumb($request->file('thumb'))
            ->setBanner($request->file('banner'))
            ->setAppThumb($request->file('app_thumb'))
            ->setAppBanner($request->file('app_banner'))
            ->setIsPublished($request->is_published)
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


    public function update(CollectionRequest $request, $collection_id)
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
            ->update();
        return http_response($request, null, 201, $response);
    }

    public function destroy(Request $request, $collection_id)
    {
        try {
            $partner = $request->auth_user->getPartner();
            $response = $this->collectionService->setPartnerId($partner->id)->setCollectionId($collection_id)->delete();
        } catch (\Exception $exception) {
            return http_response($request, null, 500, null);
        }

        return http_response($request, null, 200, $response);
    }
}