<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use App\Transformers\Business\LeaveListTransformer;
use App\Transformers\Business\LeaveTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Throwable;

class LeaveSettingsController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return BusinessMember|null
     */
    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        return $auth_info['business_member'];
    }

    /**
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function index(Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $business_member = BusinessMember::find($business_member['id']);
            $leaves = $leave_types_repo->getAllLeaveTypesByBusiness($business_member->business);
            $leave_types = $leaves->map(function ($leave) {
                return collect($leave->toArray())
                    ->only(['id', 'title', 'total_days'])
                    ->all();
            });
            return api_response($request, null, 200, ['leave_types' => $leave_types]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function store(Request $request,LeaveTypesRepoInterface $leave_types_repo)
    {
        try {
            $this->validate($request, [
                'title' => 'required',
                'total_days' => 'required'
            ]);
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $data = [
                'business_id' => $business_member['business_id'],
                'title' => $request->title,
                'total_days' => $request->total_days,
                'created_by' => $business_member['id']
            ];
            $leave_setting = $leave_types_repo->create($data);
           return api_response($request, null, 200, ['leave_setting' => $leave_setting->id]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    /**
     * @param $leave_setting
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function update($leave_setting, Request $request,LeaveTypesRepoInterface $leave_types_repo)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $leave_setting = $leave_types_repo->find($leave_setting);
            dd($request->title);
            return api_response($request, null, 200, ['info' => "Data Updated Successfully"]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
