<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
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
use Sheba\Business\ApprovalSetting\MakeDefaultApprovalSetting;
use Sheba\Business\ApprovalSetting\Updater;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSettingModule\Modules;
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
     * @var MakeDefaultApprovalSetting
     */
    private $defaultApprovalSetting;

    /**
     * ApprovalSettingsController constructor.
     * @param ApprovalSettingRepository $approval_settings_repo
     * @param ApprovalSettingRequester $approval_setting_requester
     * @param MakeDefaultApprovalSetting $default_approval_setting
     */
    public function __construct(ApprovalSettingRepository $approval_settings_repo,
                                ApprovalSettingRequester $approval_setting_requester,
                                MakeDefaultApprovalSetting $default_approval_setting)
    {
        $this->approvalSettingsRepo = $approval_settings_repo;
        $this->approvalSettingsRequester = $approval_setting_requester;
        $this->defaultApprovalSetting = $default_approval_setting;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        list($offset, $limit) = calculatePagination($request);
        $approval_settings = $this->approvalSettingsRepo->where('business_id', $business->id)->orderBy('id', 'desc');


        if ($request->has('type') && $request->has('target_id')) {
            $approval_settings = $approval_settings->where([['target_type', '=', $request->type], ['target_id', '=', $request->target_id]]);
        }
        if ($request->has('type') && $request->type) {
            $approval_settings = $approval_settings->where('target_type', $request->type);
        }
        if ($request->has('module')) {
            $approval_settings = $approval_settings->whereHas('modules', function ($q) use ($request) {
                $q->whereIn('modules', json_decode($request->module, 1));
            });
        }

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($approval_settings->get(), new ApprovalSettingListTransformer());
        $approval_settings = $manager->createData($resource)->toArray()['data'];

        $approval_settings_list_with_global = [];
        foreach ($approval_settings as $item) {
            if ($item['is_default'] == 1) $approval_settings_list_with_global[] = $item;
        }
        $approval_settings_list_without_global = [];
        foreach ($approval_settings as $item) {
            if ($item['is_default'] == 0) $approval_settings_list_without_global[] = $item;
        }
        $approval_settings_list = array_merge($approval_settings_list_with_global, $approval_settings_list_without_global);
        $is_default_already_exist = array_key_exists(1, array_flip(array_column($approval_settings_list, 'is_default')));

        if (!$is_default_already_exist) {
            $default_approval_setting = $this->defaultApprovalSetting->getApprovalSettings();
            $approval_settings_list = array_merge([$default_approval_setting], $approval_settings_list);
        }

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
        /** @var Business $business */
        $business = $request->business;
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->validate($request, [
            'modules' => 'required',
            'note' => 'string',
            'target_type' => 'required|in:' . implode(',', Targets::get()),
            'target_id' => 'required_if:target_type,in,' . implode(',', [Targets::DEPARTMENT, Targets::EMPLOYEE]),
            'approvers' => 'required',
            'is_default' => 'required|in:1,0',
        ]);
        $this->setModifier($manager_member);
        $this->approvalSettingsRequester->setBusiness($business)
            ->setIsDefault($request->is_default)
            ->setModules($request->modules)
            ->setTargetType($request->target_type)
            ->setTargetId($request->target_id)
            ->setNote($request->note)
            ->setApprovers($request->approvers)
            ->checkValidation();

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
     * @return JsonResponse
     */
    public function showDefault(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $default_approval_setting = $this->defaultApprovalSetting->getApprovalSettings();
        return api_response($request, null, 200, ['data' => $default_approval_setting]);
    }

    /**
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function update(Request $request, Updater $updater)
    {
        /** @var Business $business */
        $business = $request->business;
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
            'is_default' => 'required|in:1,0'
        ]);

        $approval_settings = $this->approvalSettingsRepo->find($request->setting);
        if (!$approval_settings) return api_response($request, null, 404);

        $this->setModifier($manager_member);
        $this->approvalSettingsRequester->setBusiness($business)
            ->setIsDefault($request->is_default)
            ->setModules($request->modules)
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
        /** @var ApprovalSetting $approval_setting */
        $approval_setting = $this->approvalSettingsRepo->find($request->setting);
        if (!$approval_setting) return api_response($request, null, 404);
        $approval_setting->delete();
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getModules(Request $request)
    {
        $modules = Modules::get();
        return api_response($request, null, 200, ['modules' => $modules]);
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
