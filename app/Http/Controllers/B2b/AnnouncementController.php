<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Sheba\Business\ACL\AccessControl;
use App\Transformers\Business\AnnouncementTransformer;
use Carbon\Carbon;
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
        $announcement_list->setBusinessId($business_member->business_id)->setOffset($offset)->setLimit($limit);
        if ($request->type) $announcement_list->setType($request->type);
        $announcements = $announcement_list->get();
        if (count($announcements) == 0) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new AnnouncementTransformer());
        $announcements = $manager->createData($announcements)->toArray()['data'];
        return api_response($request, $announcements, 200, ['announcements' => $announcements]);
    }

    public function store($business, Request $request, Creator $creator, AccessControl $access_control)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'end_date' => 'required|date|after:' . Carbon::yesterday()->format('Y-m-d'),
            'type' => 'required|string|in:' . implode(',', AnnouncementTypes::get())
        ]);
        $this->setModifier($request->business_member);
        if (!$access_control->setBusinessMember($request->business_member)->hasAccess('announcement.rw')) return api_response($request, null, 403);
        $announcement = $creator->setBusiness($request->business)->setTitle($request->title)->setEndDate(Carbon::parse($request->end_date.' 23:59:59')->toDateTimeString())
            ->setShortDescription($request->description)->setType($request->type)
            ->create();
        return api_response($request, $announcement, 200, ['id' => $announcement->id]);
    }

    public function show($business, $announcement, Request $request)
    {
        $announcement = $this->repo->find($announcement);
        if (!$announcement || $announcement->business_id != $business) return api_response($request, null, 403);
        return api_response($request, $announcement, 200, ['announcement' => [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => $announcement->type,
            'description' => $announcement->short_description,
            'end_date' => $announcement->end_date->toDateTimeString()
        ]]);
    }

    public function update($business, $announcement, Request $request, Updater $updater, AccessControl $access_control)
    {
        $this->validate($request, [
            'title' => 'string',
            'description' => 'string',
            'end_date' => 'date|after:' . Carbon::yesterday()->format('Y-m-d')
        ]);
        $business_member = $request->business_member;
        $this->setModifier($business_member);
        $announcement = $this->repo->find($announcement);
        if (!$announcement || $announcement->business_id != $business || !$access_control->setBusinessMember($business_member)->hasAccess('announcement.rw')) return api_response($request, null, 403);
        $updater->setAnnouncement($announcement);
        if ($request->has('title')) $updater->setTitle($request->title);
        if ($request->has('description')) $updater->setShortDescription($request->description);
        if ($request->has('end_date')) $updater->setEndDate(Carbon::parse($request->end_date));
        $updater->update();
        return api_response($request, $announcement, 200);
    }
}
