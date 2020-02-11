<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Transformers\Business\LeaveTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;

class LeaveController extends Controller
{
    public function getLeaveTypes(Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $leave_types = $leave_types_repo->getAllLeaveTypesByBusiness($business_member->business);

        return api_response($request, null, 200, ['leave_types' => $leave_types]);
    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return BusinessMember::find($business_member['id']);
    }

    public function store(Request $request, LeaveCreator $leave_creator)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);

            $leave = $leave_creator->setTitle($request->title)
                ->setBusinessMemberId($business_member->id)
                ->setLeaveTypeId($request->leave_type_id)
                ->setStartDate($request->start_date)
                ->setEndDate($request->end_date)
                ->create();
            return api_response($request, null,200,['leave' => $leave->id]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($leave, Request $request, LeaveRepoInterface $leave_repo)
    {
        $leave = $leave_repo->find($leave);
//        dd($leave);
        $business_member = $this->getBusinessMember($request);
        if (!$leave || $leave->business_member_id != $business_member->id) return api_response($request, null, 403);
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($leave, new LeaveTransformer());
        return api_response($request, $leave, 200, ['leave' => $fractal->createData($resource)->toArray()['data']]);
    }
}
