<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\AnnouncementV2\CreatorRequester;
use App\Sheba\Business\AnnouncementV2\Creator;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Sheba\Business\AnnouncementV2\Updater;
use App\Transformers\Business\AnnouncementListTransformer;
use App\Transformers\Business\AnnouncementShowTransformer;
use App\Transformers\Business\AnnouncementTransformer;
use App\Transformers\Business\ApprovalSettingDetailsTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\ModificationFields;

class AnnouncementV2Controller extends Controller
{
    use ModificationFields;

    /** @var AnnouncementRepositoryInterface $announcementRepo */
    private $announcementRepo;

    public function __construct(AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->announcementRepo = $announcement_repository;
    }


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
        list($offset, $limit) = calculatePagination($request);

        $announcements = $business->announcements()->orderBy('id', 'desc');

        if ($request->has('type')) $announcements = $announcements->where('type', $request->type);

        $announcements = $announcements->get();
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new AnnouncementListTransformer());
        $announcements = collect($manager->createData($announcements)->toArray()['data']);

        if ($request->has('search')) $announcements = $this->searchTitle($announcements, $request);

        if ($request->has('sort_by_created_on')) $announcements = $this->sortByCreatedOn($announcements, $request->sort_by_created_on)->values();
        if ($request->has('sort_by_target_type')) $announcements = $this->sortByTargetType($announcements, $request->sort_by_target_type)->values();


        $total_announcements = $announcements->count();
        #$limit = $this->getLimit($request, $limit, $total_announcements);
        if ($request->has('limit')) $announcements = collect($announcements)->splice($offset, $limit);

        return api_response($request, $announcements, 200, [
            'announcements' => $announcements,
            'total_announcements' => $total_announcements
        ]);
    }

    public function show($business, $announcement, Request $request)
    {
        $announcement = $this->announcementRepo->find($announcement);
        if (!$announcement || $announcement->business_id != $business)
            return api_response($request, null, 403);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($announcement, new AnnouncementShowTransformer());
        $announcement = $manager->createData($resource)->toArray()['data'];

        return api_response($request, $announcement, 200, ['announcement' => $announcement]);
    }

    public function update($business_id, $announcement_id, Request $request, CreatorRequester $creator_requester, Updater $updater)
    {
        /** @var  Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        /** @var Member $manager_member */
        $manager_member = $request->managerMember;
        $this->setModifier($manager_member);
        $announcement = $this->announcementRepo->find($announcement_id);
        if (!$announcement) return api_response($request, null, 404);
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
            ->setStatus($request->status)
            ->setAnnouncement($announcement);
        $announcement_update = $updater->setRequest($creator_requester)->update();
        if ($announcement_update == false) return api_response($request, null, 400, ['message' => 'You cannot edit an expired announcement.']);
        return api_response($request, null, 200);
    }

    private function getLimit(Request $request, $limit, $total_announcements)
    {
        if ($request->has('limit') && $request->limit == 'all') return $total_announcements;
        return $limit;
    }

    private function searchTitle($announcements, Request $request)
    {
        return $announcements->filter(function ($announcement) use ($request) {
            return str_contains(strtoupper($announcement['title']), strtoupper($request->search));
        });
    }

    private function sortByCreatedOn($announcements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($announcements)->$sort_by(function ($announcements, $key) {
            return strtoupper($announcements['created_at']);
        });
    }

    private function sortByTargetType($announcements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($announcements)->$sort_by(function ($announcements, $key) {
            return strtoupper($announcements['target_type']);
        });
    }
}
