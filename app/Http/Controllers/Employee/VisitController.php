<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use App\Sheba\EmployeeTracking\Creator;
use App\Sheba\EmployeeTracking\Requester;
use App\Sheba\EmployeeTracking\Updater;
use App\Transformers\Business\CoWorkerManagerListTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;
use App\Sheba\Business\BusinessBasicInformation;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\Visit\VisitRepository;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\BusinessMemberRepository;

class VisitController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    public function getManagerSubordinateEmployeeList(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $managers_data = (new ManagerSubordinateEmployeeList())->get($business_member);
        return api_response($request, null, 200, ['manager_list' => $managers_data]);
    }

    public function create(Request $request, Requester $requester, Creator $creator)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'employee' => 'required|numeric',
            'title' => 'required|string',
            'description' => 'sometimes|required|string',
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->getMember($request);
        $this->setModifier($member);
        $requester->setBusinessMember($business_member)->setDate($request->date)->setEmployee($request->employee)->setTitle($request->title)->setDescription($request->description);
        $creator->setRequester($requester)->create();
        return api_response($request, null, 200);
    }

    public function update($visit_id, Request $request, Requester $requester, Updater $updater, VisitRepository $visit_repository)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'employee' => 'required|numeric',
            'title' => 'required|string',
            'description' => 'sometimes|required|string',
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee_visit = $visit_repository->find($visit_id);
        if (!$employee_visit) return api_response($request, null, 404);
        $member = $this->getMember($request);
        $this->setModifier($member);
        $requester->setBusinessMember($business_member)->setEmployeeVisit($employee_visit)->setDate($request->date)->setEmployee($request->employee)->setTitle($request->title)->setDescription($request->description);
        $updater->setRequester($requester)->update();
        return api_response($request, null, 200);
    }
}