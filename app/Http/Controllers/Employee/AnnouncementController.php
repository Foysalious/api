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
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\AnnouncementTypes;

class AnnouncementController extends Controller
{
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
            $announcements = $announcements->filter(function ($announcement) use ($request) {
                return $announcement['status'] == $request->status;
            });
        }

        return api_response($request, $announcements, 200, ['announcements' => $announcements->values()]);
    }
}
