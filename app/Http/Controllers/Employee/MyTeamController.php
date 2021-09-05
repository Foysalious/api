<?php namespace App\Http\Controllers\Employee;

use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use App\Sheba\Business\BusinessBasicInformation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sheba\ModificationFields;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class MyTeamController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function myTeam(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $my_team = (new ManagerSubordinateEmployeeList())->get($business_member);
        if ($request->has('search')) $my_team = $this->searchWithEmployeeName($my_team, $request);
        $total_team = count($my_team);
        if (count($my_team) > 0) return api_response($request, $my_team, 200, [
            'my_team' => $my_team,
            'total_team' => $total_team
        ]);

        return api_response($request, null, 404);
    }

    /**
     * @param $my_team
     * @param Request $request
     * @return Collection
     */
    private function searchWithEmployeeName($my_team, Request $request)
    {
        return collect($my_team)->filter(function ($team) use ($request) {
            return str_contains(strtoupper($team['name']), strtoupper($request->search));
        });
    }
}