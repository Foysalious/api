<?php namespace App\Http\Controllers\Employee;

use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use App\Transformers\Business\MySubordinateDetailsTransformer;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\CustomSerializer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class MyTeamController extends Controller
{
    use BusinessBasicInformation, ModificationFields;
    
    /** @var ManagerSubordinateEmployeeList $subordinateEmployeeList */
    private $subordinateEmployeeList;
    /** @var BusinessMemberRepositoryInterface $businessMemberRepo */
    private $businessMemberRepo;

    /**
     * @param ManagerSubordinateEmployeeList $subordinate_employee_list
     * @param BusinessMemberRepositoryInterface $businessMember_repository
     */
    public function __construct(ManagerSubordinateEmployeeList $subordinate_employee_list, BusinessMemberRepositoryInterface $businessMember_repository)
    {
        $this->subordinateEmployeeList = $subordinate_employee_list;
        $this->businessMemberRepo = $businessMember_repository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function myTeam(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $my_team = $this->subordinateEmployeeList->get($business_member);
        if ($request->has('search')) $my_team = $this->searchWithEmployeeName($my_team, $request);
        $total_team = count($my_team);
        if (count($my_team) > 0) return api_response($request, $my_team, 200, [
            'my_team' => $my_team,
            'total_team' => $total_team
        ]);

        return api_response($request, null, 404);
    }

    public function employeeDetails($business_member_id, Request $request)
    {
        $this->validate($request, ['year' => 'required|string', 'month' => 'required|string']);
        $year = $request->year;
        $month = $request->month;

        $business_member = $this->businessMemberRepo->find($business_member_id);
        if (!$business_member) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new MySubordinateDetailsTransformer($year, $month));
        $employee_data = $manager->createData($resource)->toArray()['data'];

        if (count($employee_data) > 0) return api_response($request, $employee_data, 200, ['employee_data' => $employee_data]);

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