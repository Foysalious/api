<?php namespace App\Http\Controllers\Resource;

use App\Models\Category;
use App\Models\Partner;
use App\Models\Resource;
use App\Transformers\CustomSerializer;
use App\Transformers\Resource\ResourceProfileTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Schedule\ScheduleSlot;

class ResourceController extends Controller
{
    public function getProfile(Request $request)
    {
            $resource = Resource::find(1300); //TODO: Need to get resource_id from Auth Middleware
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Item($resource, new ResourceProfileTransformer());
            $profile = $fractal->createData($resource)->toArray()['data'];

            return api_response($request, $profile, 200, ['profile' => $profile]);
    }

    public function getSchedules(Request $request, ScheduleSlot $slot)
    {
        $this->validate($request, [
            'limit' => 'sometimes|required|numeric:min:1'
        ]);
        //TODO: Need to get resource_id, category_id and partner_id from Auth Middleware
        $resource = Resource::find(44994);
        $slot->setCategory(Category::find(14));
        $slot->setPartner(Partner::find($resource->firstPartner()->id));
        $slot->setLimit($request->limit);
        $dates = $slot->getSchedulesByResource($resource);

        return api_response($request, $dates, 200, ['dates' => $dates]);

    }
}
