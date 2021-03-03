<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Sheba\Business\ACL\AccessControl;
use App\Transformers\Business\AnnouncementTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\Announcement\AnnouncementList;
use Sheba\Business\Announcement\Creator;
use Sheba\Business\Announcement\Updater;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\AnnouncementTypes;
use Sheba\ModificationFields;

class AnnouncementController extends Controller
{
    use ModificationFields;

    /** @var AnnouncementRepositoryInterface */
    private $repo;

    public function __construct(AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->repo = $announcement_repository;
    }

    public function index($business, Request $request, AnnouncementList $announcement_list)
    {
        $this->validate($request, [
            'limit' => 'numeric',
            'offset' => 'numeric',
            'type' => 'string|in:' . implode(',', AnnouncementTypes::get())
        ]);
        $business_member = $request->business_member;
        $this->setModifier($business_member);
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $announcement_list->setBusinessId($business_member->business_id);
        if ($request->type) $announcement_list->setType($request->type);
        $announcements = $announcement_list->get();
        if (count($announcements) == 0) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new AnnouncementTransformer());
        $announcements = collect($manager->createData($announcements)->toArray()['data']);
        if ($request->has('status')) {
            $announcements = $announcements->filter(function ($announcement) use ($request) {
                return $announcement['status'] == $request->status;
            });
        }
        $totalAnnouncements = $announcements->count();
        if ($request->has('limit')) $announcements = $announcements->splice($offset, $limit);
        return api_response($request, $announcements, 200, [
            'announcements' => $announcements->values(),
            'totalAnnouncements' => $totalAnnouncements
        ]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param Creator $creator
     * @param AccessControl $access_control
     * @return JsonResponse
     */
    public function store($business, Request $request, Creator $creator, AccessControl $access_control)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'end_date' => 'required|date|after:' . Carbon::yesterday()->format('Y-m-d'),
            'type' => 'required|string|in:' . implode(',', AnnouncementTypes::get())
        ]);

        if (!$access_control->setBusinessMember($request->business_member)->hasAccess('announcement.rw'))
            return api_response($request, null, 403);

        $end_date = Carbon::parse($request->end_date . ' 23:59:59')->toDateTimeString();
        $announcement = $creator->setBusiness($request->business)
            ->setBusinessMember($request->business_member)
            ->setTitle($request->title)
            ->setEndDate($end_date)
            ->setShortDescription($request->short_description)
            ->setLongDescription($request->description)
            ->setType($request->type)
            ->create();

        return api_response($request, $announcement, 200, ['id' => $announcement->id]);
    }

    /**
     * @param $business
     * @param $announcement
     * @param Request $request
     * @return JsonResponse
     */
    public function show($business, $announcement, Request $request)
    {
        $announcement = $this->repo->find($announcement);
        if (!$announcement || $announcement->business_id != $business)
            return api_response($request, null, 403);

        $announcement = [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => $announcement->type,
            'short_description' => $announcement->short_description,
            'description' => $announcement->long_description,
            'end_date' => $announcement->end_date->toDateTimeString()
        ];
        return api_response($request, $announcement, 200, ['announcement' => $announcement]);
    }

    /**
     * @param $business
     * @param $announcement
     * @param Request $request
     * @param Updater $updater
     * @param AccessControl $access_control
     * @return JsonResponse
     */
    public function update($business, $announcement, Request $request, Updater $updater, AccessControl $access_control)
    {
        $this->validate($request, [
            'title' => 'string',
            'description' => 'string',
            'end_date' => 'date|after:' . Carbon::yesterday()->format('Y-m-d')
        ]);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $announcement = $this->repo->find($announcement);
        if (!$announcement || $announcement->business_id != $business || !$access_control->setBusinessMember($business_member)->hasAccess('announcement.rw'))
            return api_response($request, null, 403);

        $updater->setAnnouncement($announcement);

        if ($request->has('title')) $updater->setTitle($request->title);
        if ($request->has('type')) $updater->setType($request->type);
        if ($request->has('short_description')) $updater->setShortDescription($request->short_description);
        if ($request->has('description')) $updater->setLongDescription($request->description);
        if ($request->has('end_date')) $updater->setEndDate(Carbon::parse($request->end_date . ' 23:59:59'));

        $updater->update();

        return api_response($request, $announcement, 200);
    }
}
