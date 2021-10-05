<?php namespace App\Http\Controllers\Employee;

use App\Models\Business;
use App\Transformers\Business\StickerCategoryList;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\BusinessEmployeesTransformer;
use App\Transformers\CustomSerializer;
use League\Fractal\Resource\Item;
use Sheba\Dal\StickerCategory\StickerCategory;
use App\Sheba\Business\Appreciation\Creator;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class AppreciateController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        /** @var Business $business */
        $business = $this->getBusiness($request);
        $business_members = $this->accessibleBusinessMembers($business, $request);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_members->get(), new BusinessEmployeesTransformer());
        $employees_with_dept_data = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, [
            'employees' => $employees_with_dept_data['employees'],
            'departments' => $employees_with_dept_data['departments']
        ]);
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, Creator $creator)
    {
        $this->validate($request, [
            'sticker' => 'required',
            'receiver_id' => 'required'
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $this->setModifier($business_member->member);

        $creator->setSticker($request->sticker)
            ->setReceiver($request->receiver_id)
            ->setGiver($business_member->id)
            ->setComplement($request->complement)
            ->create();

        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryWiseStickers(Request $request)
    {
        $sticker_categories = StickerCategory::all(['id', 'name', 'title']);

        $fractal = new Manager();
        $resource = new Collection($sticker_categories, new StickerCategoryList());
        $sticker_categories = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $sticker_categories, 200, ['stickers' => $sticker_categories]);
    }
}