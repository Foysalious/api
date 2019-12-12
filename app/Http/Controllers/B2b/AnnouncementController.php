<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Sheba\Business\ACL\AccessControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Business\Announcement\Creator;
use Sheba\Business\Announcement\Updater;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\ModificationFields;

class AnnouncementController extends Controller
{
    use ModificationFields;

    public function store($business, Request $request, Creator $creator, AccessControl $access_control)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'end_date' => 'required|date|after:' . Carbon::yesterday()->format('Y-m-d')
        ]);
        $this->setModifier($request->business_member);
        if (!$access_control->setBusinessMember($request->business_member)->hasAccess('announcement.rw')) return api_response($request, null, 403);
        $announcement = $creator->setBusiness($request->business)->setTitle($request->title)->setEndDate(Carbon::parse($request->end_date))->setShortDescription($request->description)
            ->create();
        return api_response($request, $announcement, 200, ['id' => $announcement->id]);
    }

    public function show($business, $announcement, Request $request, AnnouncementRepositoryInterface $announcement_repository)
    {
        $announcement = $announcement_repository->find($announcement);
        if (!$announcement || $announcement->business_id != $business) return api_response($request, null, 403);
        return api_response($request, $announcement, 200, ['announcement' => [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'description' => $announcement->short_description,
            'end_date' => $announcement->end_date->toDateTimeString()
        ]]);
    }

    public function update($business, $announcement, Request $request, Updater $updater, AccessControl $access_control, AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->validate($request, [
            'title' => 'string',
            'description' => 'string',
            'end_date' => 'date|after:' . Carbon::yesterday()->format('Y-m-d')
        ]);
        $business_member = $request->business_member;
        $this->setModifier($business_member);
        $announcement = $announcement_repository->find($announcement);
        if (!$announcement || $announcement->business_id != $business || !$access_control->setBusinessMember($business_member)->hasAccess('announcement.rw')) return api_response($request, null, 403);
        $updater->setAnnouncement($announcement);
        if ($request->has('title')) $updater->setTitle($request->title);
        if ($request->has('description')) $updater->setShortDescription($request->description);
        if ($request->has('end_date')) $updater->setEndDate(Carbon::parse($request->end_date));
        $updater->update();
        return api_response($request, $announcement, 200);
    }
}