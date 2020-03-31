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
use Sheba\Resource\Schedule\ResourceScheduleSlot;

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

    public function getSchedules(Job $job, Request $request, ResourceScheduleSlot $slot)
    {
        $resource = $request->auth_user->getResource();
        $category = Category::find($job->category->id);
        $slot->setCategory($category);
        $slot->setPartner(Partner::find($job->partner_order->partner_id));
        $slot->setLimit(7);
        $dates = $slot->getSchedulesByResource($resource);

        return api_response($request, $dates, 200, ['dates' => $dates]);

    }

    public function getHome(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $info = [
            'name' => $resource->profile->name,
            'picture' => $resource->profile->pro_pic,
            'is_verified' => $resource->is_verified,
            'rating' => 4.2,
            'notification_count' => 2
        ];
        return api_response($request, $info, 200, ['home' => $info]);
    }
}
