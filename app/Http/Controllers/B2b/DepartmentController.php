<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\Department\CreateRequest;
use Sheba\Business\Department\UpdateRequest;
use Sheba\Business\Department\Creator;
use Sheba\Business\Department\Updater;
use App\Http\Controllers\Controller;
use App\Models\BusinessDepartment;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ModificationFields;
    /** @var CreateRequest $departmentCreateRequest */
    private $departmentCreateRequest;
    /** @var Creator $creator */
    private $creator;
    /** @var UpdateRequest $departmentUpdateRequest */
    private $departmentUpdateRequest;
    /** @var Updater $updater */
    private $updater;

    public function __construct(CreateRequest $create_request, Creator $creator, UpdateRequest $update_request, Updater $updater)
    {
        $this->departmentCreateRequest = $create_request;
        $this->creator = $creator;
        $this->departmentUpdateRequest = $update_request;
        $this->updater = $updater;
    }

    public function index($business, Request $request)
    {
        $business = $request->business;
        $business_departments = BusinessDepartment::published()->where('business_id', $business->id)->select('id', 'business_id', 'name', 'created_at')->orderBy('id', 'DESC')->get();
        $departments = [];
        foreach ($business_departments as $business_department) {
            $department = [
                'id' => $business_department->id, 'name' => $business_department->name, 'created_at' => $business_department->created_at->format('d/m/y')
            ];
            array_push($departments, $department);
        }

        if (count($departments) > 0)
            return api_response($request, $departments, 200, ['departments' => $departments]);

        return api_response($request, null, 404);
    }

    public function store($business, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'abbreviation' => 'required|string'
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $this->departmentCreateRequest->setBusiness($business)
            ->setDepartmentName($request->name)
            ->setAbbreviation($request->abbreviation);
        $this->creator->setDepartmentCreateRequest($this->departmentCreateRequest)->create();
        return api_response($request, null, 200);
    }

    public function update($business, $id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'abbreviation' => 'required|string'
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $this->departmentUpdateRequest->setBusiness($business)
            ->setDepartmentName($request->name)
            ->setAbbreviation($request->abbreviation);
        $this->updater->setDepartmentUpdateRequest($this->departmentUpdateRequest)->update();
        return api_response($request, null, 200);
    }

    public function destroy($business, $id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'abbreviation' => 'required|string'
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        return api_response($request, null, 200);
    }
}