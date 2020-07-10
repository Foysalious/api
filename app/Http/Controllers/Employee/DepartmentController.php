<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessDepartment;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use BusinessBasicInformation;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $business = $this->getBusiness($request);
        if (!$business) return api_response($request, null, 404);

        $departments = BusinessDepartment::published()
            ->where('business_id', $business->id)
            ->select('id', 'name')
            ->orderBy('id', 'DESC')
            ->get();

        if ($departments->isEmpty())
            return api_response($request, null, 404);

        return api_response($request, null, 200, ['departments' => $departments]);
    }
}
