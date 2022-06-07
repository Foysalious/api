<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessDepartment;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\LiveTracking\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use BusinessBasicInformation;

    /**
     * @param Request $request
     * @param Activity $activity
     * @return JsonResponse
     */
    public function index(Request $request, Activity $activity)
    {
        $business = $this->getBusiness($request);
        if (!$business) return api_response($request, null, 404);

        $departments = $business->departments()->published()
            ->select('id', 'name')
            ->orderBy('id', 'DESC')
            ->get();
        if ($departments->isEmpty()) return api_response($request, null, 404);

        if ($request->has('with') && $request->with == "activity") {
            $activity = $activity->getActivity();
            return api_response($request, null, 200, [
                'departments' => $departments,
                'activity' => $activity
            ]);
        }
        return api_response($request, null, 200, ['departments' => $departments,]);
    }
}
