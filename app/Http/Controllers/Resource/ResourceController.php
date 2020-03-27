<?php namespace App\Http\Controllers\Resource;

use App\Models\Category;
use App\Models\Job;
use App\Models\Partner;
use App\Models\Resource;
use App\Transformers\CustomSerializer;
use App\Transformers\Resource\ResourceProfileTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Authentication\AuthUser;
use Sheba\Schedule\ScheduleSlot;

class ResourceController extends Controller
{
    public function getProfile(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($resource, new ResourceProfileTransformer());
        $profile = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $profile, 200, ['profile' => $profile]);
    }

    public function getSchedules(Job $job, Request $request, ScheduleSlot $slot)
    {
        $this->validate($request, [
            'limit' => 'sometimes|required|numeric:min:1'
        ]);
        //TODO: Need to get resource_id, category_id and partner_id from Auth Middleware
        $resource = $job->resource_id;
        $slot->setCategory(Category::find($job->category->id));
        $slot->setPartner(Partner::find($job->partner_order->partner_id));
        $slot->setLimit($request->limit);
        $dates = $slot->getSchedulesByResource(Resource::find($resource));

        return api_response($request, $dates, 200, ['dates' => $dates]);

    }
}
