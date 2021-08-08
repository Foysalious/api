<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use App\Transformers\Business\CoWorkerManagerListTransformer;
use App\Transformers\BusinessEmployeeDetailsTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;
use App\Sheba\Business\BusinessBasicInformation;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\BusinessMemberRepository;

class EmployeeVisitTrackingController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /*** @var BusinessMemberRepository $businessMemberRepository*/
    private $businessMemberRepository;

    public function __construct(BusinessMemberRepository $business_member_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
    }

    public function getCoWorkerManagerList(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $first_level_managers = $this->getCoWorkersUnderSpecificManager($business_member->id);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $managers_data = [];
        if ($first_level_managers->count() > 0)
            foreach ($first_level_managers as $first_level_manager){
                $resource = new Item($first_level_manager, new CoWorkerManagerListTransformer());
                $managers_data[] = $manager->createData($resource)->toArray()['data'];
                $second_level_managers = $this->getCoWorkersUnderSpecificManager($first_level_manager->id);
                if ($second_level_managers->count() > 0)
                foreach ($second_level_managers as $second_level_manager){
                    $resource = new Item($second_level_manager, new CoWorkerManagerListTransformer());
                    $managers_data[] = $manager->createData($resource)->toArray()['data'];
                    $third_level_managers = $this->getCoWorkersUnderSpecificManager($second_level_manager->id);
                    if ($third_level_managers->count() > 0)
                    foreach ($third_level_managers as $third_level_manager){
                        $resource = new Item($third_level_manager, new CoWorkerManagerListTransformer());
                        $managers_data[] = $manager->createData($resource)->toArray()['data'];
                    }
                }
        }
        return api_response($request, null, 200, ['manager_list' => $managers_data]);
    }

    private function getCoWorkersUnderSpecificManager($business_member_id)
    {
        return $this->businessMemberRepository->where('manager_id', $business_member_id)->get();
    }

}