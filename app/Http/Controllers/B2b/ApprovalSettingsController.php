<?php namespace App\Http\Controllers\B2b;

use App\Transformers\Business\ApprovalSettingTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\ApprovalSetting\ApprovalSettingRequester;
use Sheba\Business\ApprovalSetting\Creator;
use Sheba\ModificationFields;
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
     * @param Request $request
     * @param ApprovalSettingRepository $approval_settings_repo
     * @param DepartmentRepositoryInterface $department_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ProfileRepository $profile_repo
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, ApprovalSettingRepository $approval_settings_repo, DepartmentRepositoryInterface $department_repo, BusinessMemberRepositoryInterface $business_member_repo, ProfileRepository $profile_repo)
    {
        $approval_settings =  $approval_settings_repo->where('business_id', $request->business->id)->get();
        
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $approval_settings_data = new Item($approval_settings, new ApprovalSettingTransformer($department_repo, $business_member_repo, $profile_repo));
        $approval_settings_list = collect($manager->createData($approval_settings_data)->toArray());

        return api_response($request, null, 200, ['data' => $approval_settings_list]);
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
}
