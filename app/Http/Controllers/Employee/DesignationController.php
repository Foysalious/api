<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessDepartment;
use Sheba\Business\CoWorker\Designations;
use App\Models\BusinessRole;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
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

        $business_department_ids = BusinessDepartment::query()->where('business_id', $business->id)->pluck('id')->toArray();
        $roles = BusinessRole::query()->whereIn('business_department_id', $business_department_ids)->pluck('name')->toArray();
        $designations_list = Designations::getDesignations();
        $all_roles = collect(array_merge($roles,$designations_list))->unique();

        if ($request->filled('search')) {
            $all_roles = array_filter($all_roles->toArray(), function ($role) use ($request) {
                return str_contains(strtoupper($role), strtoupper($request->search));
            });
        }
        
        return api_response($request, null, 200, ['designations' => collect($all_roles)->values()]);
    }
}