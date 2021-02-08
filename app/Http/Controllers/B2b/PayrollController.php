<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\PayrollSetting\Requester as PayrollSettingRequester;
use Sheba\Business\PayrollSetting\Updater as PayrollSettingUpdater;
use Sheba\Business\PayrollComponent\Updater as PayrollComponentUpdater;
use App\Transformers\Business\PayrollSettingsTransformer;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use App\Transformers\CustomSerializer;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Item;
use Illuminate\Http\JsonResponse;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use App\Models\Business;

class PayrollController extends Controller
{
    use ModificationFields;

    private $payrollSettingRepository;
    private $payrollSettingRequester;
    private $payrollSettingUpdater;
    private $payrollComponentUpdater;

    /**
     * PayrollController constructor.
     * @param PayrollSettingRepository $payroll_setting_repository
     * @param PayrollSettingRequester $payroll_setting_requester
     * @param PayrollSettingUpdater $payroll_setting_updater
     * @param PayrollComponentUpdater $payroll_component_updater
     */
    public function __construct(PayrollSettingRepository $payroll_setting_repository,
                                PayrollSettingRequester $payroll_setting_requester,
                                PayrollSettingUpdater $payroll_setting_updater,
                                PayrollComponentUpdater $payroll_component_updater)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        $this->payrollSettingRequester = $payroll_setting_requester;
        $this->payrollSettingUpdater = $payroll_setting_updater;
        $this->payrollComponentUpdater = $payroll_component_updater;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPayrollSettings(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        /** @var PayrollSetting $payroll_setting */
        $payroll_setting = $business->payrollSetting;

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($payroll_setting, new PayrollSettingsTransformer());
        $payroll_setting = $manager->createData($member)->toArray()['data'];

        return api_response($request, null, 200, ['payroll_setting' => $payroll_setting]);
    }

    /**
     * @param $business
     * @param $payroll_setting
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePaySchedule($business, $payroll_setting, Request $request)
    {
        $this->validate($request, [
            'is_enable' => 'required|integer',
            'pay_day' => 'required|integer'
        ]);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        if (!$payroll_setting) return api_response($request, null, 404);
        $this->payrollSettingRequester->setIsEnable($request->is_enable)->setPayDay($request->pay_day);
        $this->payrollSettingUpdater->setPayrollSetting($payroll_setting)->setPayrollSettingRequest($this->payrollSettingRequester)->update();
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $payroll_setting
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSalaryBreakdown($business, $payroll_setting, Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);
        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        if (!$payroll_setting) return api_response($request, null, 404);
        $this->payrollComponentUpdater->setPayrollSetting($payroll_setting)->setGrossComponents($request->gross_components)->updateGrossComponents();
        return api_response($request, null, 200);
    }
}