<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessMember;
use Sheba\Business\PayrollSetting\Requester as PayrollSettingRequester;
use Sheba\Business\PayrollSetting\Updater as PayrollSettingUpdater;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class PayrollController extends Controller
{
    use ModificationFields;

    private $payrollSettingRepository;
    private $payrollSettingRequester;
    private $payrollSettingUpdater;

    public function __construct(PayrollSettingRepository $payroll_setting_repository,
                                PayrollSettingRequester $payroll_setting_requester,
                                PayrollSettingUpdater $payroll_setting_updater)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        $this->payrollSettingRequester = $payroll_setting_requester;
        $this->payrollSettingUpdater = $payroll_setting_updater;
    }

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

    public function updateSalaryBreakdown($business, $payroll_setting, Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);
        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        if (!$payroll_setting) return api_response($request, null, 404);

        return api_response($request, null, 200);
    }
}