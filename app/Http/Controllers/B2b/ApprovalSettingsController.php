<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Transformers\Business\ApprovalSettingDetailsTransformer;
use App\Transformers\Business\ApprovalSettingListTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Business\ApprovalSetting\ApprovalSettingRequester;
use Sheba\Business\ApprovalSetting\Creator;
use Sheba\Business\ApprovalSetting\Updater;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;

class ApprovalSettingsController extends Controller
{
    use ModificationFields;

    /**
     * @var ApprovalSettingRepository
     */
    private $approvalSettingsRepo;
    /**
     * @var ApprovalSettingRequester
     */
    private $approvalSettingsRequester;

    /**
     * ApprovalSettingsController constructor.
     * @param ApprovalSettingRepository $approval_settings_repo
     * @param ApprovalSettingRequester $approval_setting_requester
     */
    public function __construct(ApprovalSettingRepository $approval_settings_repo, ApprovalSettingRequester $approval_setting_requester)
    {
        $this->approvalSettingsRepo = $approval_settings_repo;
        $this->approvalSettingsRequester = $approval_setting_requester;

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $business = $request->business;
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        list($offset, $limit) = calculatePagination($request);
        $approval_settings = $this->approvalSettingsRepo->where('business_id', $business->id);

        if ($request->has('type') && $request->has('target_id')) $approval_settings = $approval_settings->where([['target_type', '=', $request->type], ['target_id', '=', $request->target_id]]);
        if ($request->has('type') && $request->type) $approval_settings = $approval_settings->where('target_type', $request->type);
        if ($request->has('module')) $approval_settings = $approval_settings->whereHas('modules', function ($q) use ($request) {
            $q->whereIn('modules', explode(',', $request->module));
        });
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($approval_settings->get(), new ApprovalSettingListTransformer());
        $approval_settings_list = $manager->createData($resource)->toArray()['data'];
        if ($request->has('search')) $approval_settings_list = collect($this->searchWithEmployee($approval_settings_list, $request->search))->values();
        $total_approval_settings = count($approval_settings_list);
        if ($request->has('limit')) $approval_settings_list = collect($approval_settings_list)->splice($offset, $limit);

        return api_response($request, null, 200, ['data' => $approval_settings_list, 'total_approval_settings' => $total_approval_settings]);
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, Creator $creator)
    {
        $business = $request->business;
        $manager_member = $request->manager_member;
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->validate($request, [
            'modules' => 'required',
            'note' => 'string',
            'target_type' => 'required|in:' . implode(',', Targets::get()),
            'approvers' => 'required',
        ]);

        $this->setModifier($manager_member);
        $this->approvalSettingsRequester->setModules($request->modules)
            ->setTargetType($request->target_type)
            ->setTargetId($request->target_id)
            ->setNote($request->note)
            ->setApprovers($request->approvers);

        if ($this->approvalSettingsRequester->hasError()) {
            return api_response($request, null, $this->approvalSettingsRequester->getErrorCode(), ['message' => $this->approvalSettingsRequester->getErrorMessage()]);
        }

        $creator->setApprovalSettingRequester($this->approvalSettingsRequester)->setBusiness($business)->create();
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        /** @var ApprovalSetting $approval_settings */
        $approval_settings = $this->approvalSettingsRepo->find($request->setting);
        if (!$approval_settings) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($approval_settings, new ApprovalSettingDetailsTransformer());
        $approval_settings_details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $approval_settings_details]);
    }

    /**
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function update(Request $request, Updater $updater)
    {
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->validate($request, [
            'modules' => 'sometimes|required',
            'note' => 'string',
            'target_type' => 'sometimes|required|in:' . implode(',', Targets::get()),
            'target_id' => 'required_if:target_type,in,' . implode(',', [Targets::DEPARTMENT, Targets::EMPLOYEE]),
        ]);

        $approval_settings = $this->approvalSettingsRepo->find($request->setting);
        if (!$approval_settings) return api_response($request, null, 404);

        $this->setModifier($manager_member);
        $this->approvalSettingsRequester->setModules($request->modules)
            ->setTargetType($request->target_type)
            ->setTargetId($request->target_id)
            ->setNote($request->note)
            ->setApprovers($request->approvers);

        if ($this->approvalSettingsRequester->hasError()) {
            return api_response($request, null, $this->approvalSettingsRequester->getErrorCode(), ['message' => $this->approvalSettingsRequester->getErrorMessage()]);
        }
        $updater->setApprovalSettings($approval_settings)->setApprovalSettingRequester($this->approvalSettingsRequester)->update();
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $approval_settings = $this->approvalSettingsRepo->find($request->setting);
        if (!$approval_settings) return api_response($request, null, 404);
        $approval_settings->delete();
        return api_response($request, null, 200);
    }

    /**
     * @param $approval_settings_list
     * @param $search
     * @return array
     */
    private function searchWithEmployee($approval_settings_list, $search)
    {
        return array_where($approval_settings_list, function ($key, $value) use ($search) {
            return str_contains(strtoupper($value['target_type']['employee']['name']), strtoupper($search));
        });
    }

}
