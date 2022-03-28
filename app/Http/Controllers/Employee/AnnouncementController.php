<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\Business\AnnouncementTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\Announcement\AnnouncementList;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\AnnouncementTarget;
use Sheba\Dal\Announcement\AnnouncementTypes;

class AnnouncementController extends Controller
{
    const ONGOING = 'Ongoing';
    const IS_PUBLISHED_FOR_APP = 1;

    use BusinessBasicInformation;

    public function show($announcement, Request $request, AnnouncementRepositoryInterface $announcement_repository)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        $announcement = $announcement_repository->find($announcement);
        if (!$announcement || $announcement->business_id != $business_member['business_id']) return api_response($request, null, 403);
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($announcement, new AnnouncementTransformer());
        return api_response($request, $announcement, 200, ['announcement' => $fractal->createData($resource)->toArray()['data']]);
    }

    public function index(Request $request, AnnouncementList $announcement_list)
    {

        $this->validate($request, ['limit' => 'numeric', 'offset' => 'numeric', 'type' => 'string|in:' . implode(',', AnnouncementTypes::get())]);
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 403);
        list($offset, $limit) = calculatePagination($request);
        $announcement_list->setBusinessId($business_member['business_id'])->setOffset($offset)->setLimit($limit);
        if ($request->type) $announcement_list->setType($request->type);
        $announcements = $announcement_list->get();
        if (count($announcements) == 0) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new AnnouncementTransformer());
        $announcements = collect($manager->createData($announcements)->toArray()['data']);

        if ($request->has('status')) {
            $status = $request->status == self::ONGOING ? 'Published' : null;
            $announcements = $announcements->filter(function ($announcement) use ($request, $status) {
                return $announcement['status'] == $status && $announcement['is_published_for_app'] == self::IS_PUBLISHED_FOR_APP;
            });
        }

        $announcements = $this->getFilteredAnnouncements($business_member, $announcements);

        return api_response($request, $announcements, 200, ['announcements' => $announcements->values()]);
    }

    /**
     * @param $business_member
     * @param $announcements
     * @return \Illuminate\Support\Collection
     */
    private function getFilteredAnnouncements($business_member, $announcements)
    {
        $department = $business_member->department();

        $all_announcements = [];
        foreach ($announcements as $announcement) {

            if ($announcement['target_type'] === AnnouncementTarget::ALL) {
                $all_announcements[] = $announcement;
            }
            if ($announcement['target_type'] === AnnouncementTarget::DEPARTMENT && !!$business_member->department() &&in_array($department->id, $announcement['target_id'])) {
                $all_announcements[] = $announcement;
            }
            if ($announcement['target_type'] === AnnouncementTarget::EMPLOYEE && in_array($business_member->id, $announcement['target_id'])) {
                $all_announcements[] = $announcement;
            }
            if ($announcement['target_type'] === AnnouncementTarget::EMPLOYEE_TYPE && in_array($business_member->employee_type, $announcement['target_id'])) {
                $all_announcements[] = $announcement;
            }
        }
        return collect($all_announcements);
    }
}
