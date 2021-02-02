<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\PayrollSetting\Requester as PayrollSettingRequester;
use Sheba\Business\PayrollSetting\Updater as PayrollSettingUpdater;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
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
        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        $this->payrollSettingRequester->setIsEnable($request->is_enable)->setPayDay($request->pay_day);
        $this->payrollSettingUpdater->setPayrollSetting($payroll_setting)->setPayrollSettingRequest($this->payrollSettingRequester)->update();
        return api_response($request, null, 200);
    }

    public function updateSalaryBreakdown($business, $payroll_setting, Request $request)
    {
        $this->validate($request, [
            'is_enable' => 'required|integer',
            'pay_day' => 'required|integer'
        ]);
        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);

        return api_response($request, null, 200);
    }
}