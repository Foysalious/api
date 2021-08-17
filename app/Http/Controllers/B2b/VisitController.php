<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\MyVisitListTransformer;
use App\Transformers\Business\TeamVisitListTransformer;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Sheba\Dal\Visit\Visit;
use Sheba\Dal\Visit\VisitRepository;

class VisitController extends Controller
{
    /** @var VisitRepository $visitRepository */
    private $visitRepository;

    public function __construct(VisitRepository $visit_repository)
    {
        $this->visitRepository = $visit_repository;
    }

    public function getTeamVisits(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        list($offset, $limit) = calculatePagination($request);
        $visits = Visit::with([
            'visitor' => function ($q) {
                $q->with([
                    'member' => function ($q) {
                        $q->select('members.id', 'profile_id')->with([
                            'profile' => function ($q) {
                                $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                            }
                        ]);
                    },
                    'role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with([
                            'businessDepartment' => function ($q) {
                                $q->select('business_departments.id', 'business_id', 'name');
                            }
                        ]);
                    }]);
            }
        ])->get();

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $visits = new Collection($visits, new TeamVisitListTransformer());
        $visits = collect($manager->createData($visits)->toArray()['data']);

        $total_visits = count($visits);
        #$limit = $this->getLimit($request, $limit, $total_visits);
        $visits = collect($visits)->splice($offset, $limit);
        if (count($visits) > 0) return api_response($request, $visits, 200, [
            'employees' => $visits,
            'total_employees' => $total_visits
        ]);
        return api_response($request, null, 404);
    }

    public function getMyVisits(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        list($offset, $limit) = calculatePagination($request);
        $visits = Visit::with([
            'visitor' => function ($q) {
                $q->with([
                    'member' => function ($q) {
                        $q->select('members.id', 'profile_id')->with([
                            'profile' => function ($q) {
                                $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                            }
                        ]);
                    },
                    'role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with([
                            'businessDepartment' => function ($q) {
                                $q->select('business_departments.id', 'business_id', 'name');
                            }
                        ]);
                    }]);
            }
        ])->where('visitor_id', $business_member->id)->get();

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $visits = new Collection($visits, new MyVisitListTransformer());
        $visits = collect($manager->createData($visits)->toArray()['data']);

        $total_visits = count($visits);
        #$limit = $this->getLimit($request, $limit, $total_visits);
        $visits = collect($visits)->splice($offset, $limit);
        if (count($visits) > 0) return api_response($request, $visits, 200, [
            'employees' => $visits,
            'total_employees' => $total_visits
        ]);
        return api_response($request, null, 404);
    }
}