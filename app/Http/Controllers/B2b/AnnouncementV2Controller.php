<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\AnnouncementV2\CreatorRequester;
use App\Sheba\Business\AnnouncementV2\Creator;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Transformers\Business\AnnouncementListTransformer;
use App\Transformers\Business\AnnouncementTransformer;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\ModificationFields;

class AnnouncementV2Controller extends Controller
{
    use ModificationFields;

    public function store($business, Request $request, CreatorRequester $creator_requester, Creator $creator)
    {
        /** @var  Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        /** @var Member $manager_member */
        $manager_member = $request->managerMember;
        $this->setModifier($manager_member);

        $creator_requester->setType($request->type)
            ->setTitle($request->title)
            ->setDescription($request->description)
            ->setIsPublished($request->is_published)
            ->setTargetType($request->target_type)
            ->setTargetIds($request->target_ids)
            ->setScheduledFor($request->scheduled_for)
            ->setStartDate($request->start_date)
            ->setStartTime($request->start_time)
            ->setEndDate($request->end_date)
            ->setEndTime($request->end_time)
            ->setStatus($request->status);

        $announcement = $creator->setBusiness($business)->setBusinessMember($business_member)->setRequest($creator_requester)->create();
        return api_response($request, $announcement, 200);
    }

    public function index($business, Request $request)
    {
        /** @var  Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        $announcements = $business->announcements()->orderBy('id', 'desc')->get();

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new AnnouncementListTransformer());
        $announcements = collect($manager->createData($announcements)->toArray()['data']);

        return api_response($request, $announcements, 200, ['announcements'=>$announcements]);
    }
}