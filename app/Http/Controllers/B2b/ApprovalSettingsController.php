<?php namespace App\Http\Controllers\B2b;

use App\Transformers\Business\ApprovalSettingListTransformer;
use App\Transformers\CustomSerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\ApprovalSetting\ApprovalSettingRequester;
use Sheba\Business\ApprovalSetting\Creator;
use Sheba\ModificationFields;
use Sheba\OAuth2\AuthUser;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingModule\Modules;
class ApprovalSettingsController extends Controller
{
    use ModificationFields;

    /**
     * @var ApprovalSettingRepository
     */
    private $approvalSettingsRepo;

    /**
     * ApprovalSettingsController constructor.
     * @param ApprovalSettingRepository $approval_settings_repo
     */
    public function __construct(ApprovalSettingRepository $approval_settings_repo)
    {
        $this->approvalSettingsRepo = $approval_settings_repo;

    }

    /**
     * @param Request $request
     * @param ApprovalSettingRepository $approval_settings_repo
     * @param DepartmentRepositoryInterface $department_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ProfileRepository $profile_repo
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, DepartmentRepositoryInterface $department_repo, BusinessMemberRepositoryInterface $business_member_repo, ProfileRepository $profile_repo)
    {
        list($offset, $limit) = calculatePagination($request);
        $approval_settings =  $this->approvalSettingsRepo->where('business_id', $request->business->id);

        if ($request->has('type') && $request->has('target_id')) $approval_settings = $approval_settings->where([['target_type', '=', $request->type],['target_id', '=', $request->target_id]]);
        if ($request->has('type') && $request->type) $approval_settings = $approval_settings->where('target_type', $request->type);
        if ($request->has('module')) $approval_settings = $approval_settings->whereHas('modules', function($q) use ($request){
            $q->whereIn('modules', explode(',', $request->module));
        });
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($approval_settings->get(), new ApprovalSettingListTransformer($department_repo, $business_member_repo, $profile_repo));
        $approval_settings_list = $manager->createData($resource)->toArray()['data'];

        if ($request->has('search')) $approval_settings_list = collect($this->searchWithEmployee($approval_settings_list, $request->search))->values();
        if ($request->has('limit')) $approval_settings_list = collect($approval_settings_list)->splice($offset, $limit);

        return api_response($request, null, 200, ['data' => $approval_settings_list, 'total_approval_settings' => count($approval_settings_list)]);
    }

    private function searchWithEmployee($approval_settings_list, $search)
    {
        return array_where($approval_settings_list, function ($key, $value) use ($search){
            return str_contains(strtoupper($value['target_type']['employee']['name']), strtoupper($search));
        });
    }

    public function store(Request $request, ApprovalSettingRequester $approval_setting_requester, Creator $creator)
    {
        $this->validate($request, [
            'modules' => 'required|in:' . implode(',', Modules::get()),
            'note' => 'required|string',
            'target_type' => 'required|in:' . implode(',', Targets::get()),
            'approvers' => 'required',
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $approval_setting_requester->setModules($request->modules)
            ->setTargetType($request->target_type)
            ->setTargetId($request->targetId)
            ->setNote($request->note)
            ->setApprovers($request->appovers);
        $creator->setApprovalSettingRequester($approval_setting_requester)->setBusiness($business)->create();
        return api_response($request, null, 200);

    }

    public function delete($settings, Request $request)
    {
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $approval_settings =  $this->approvalSettingsRepo->where('id', $request->settings);
        if (!$approval_settings) return api_response($request, null, 404);
        $approval_settings->delete();

        return api_response($request, null, 200, ['msg' => "Deleted Successfully"]);
    }
}
