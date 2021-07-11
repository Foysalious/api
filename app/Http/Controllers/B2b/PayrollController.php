<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\ComponentPackage\Requester;
use App\Sheba\Business\ComponentPackage\Creator as PackageCreator;
use App\Sheba\Business\ComponentPackage\Updater as PackageUpdater;
use App\Sheba\Business\PayrollComponent\Components\GrossComponents\Creator;
use App\Sheba\Business\PayrollComponent\Components\GrossComponents\Updater;
use Carbon\Carbon;
use Sheba\Business\PayrollSetting\Requester as PayrollSettingRequester;
use Sheba\Business\PayrollSetting\Updater as PayrollSettingUpdater;
use Sheba\Business\PayrollComponent\Updater as PayrollComponentUpdater;
use Sheba\Business\PayrollComponent\Requester as PayrollComponentRequester;
use App\Sheba\Business\PayrollComponent\Components\Additions\Creator as AdditionCreator;
use App\Sheba\Business\PayrollComponent\Components\Deductions\Creator as DeductionsCreator;
use App\Transformers\Business\PayrollSettingsTransformer;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollSetting\PayDayType;
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
use DB;

class PayrollController extends Controller
{
    use ModificationFields;

    private $payrollSettingRepository;
    private $payrollSettingRequester;
    private $payrollSettingUpdater;
    private $payrollComponentUpdater;
    /*** @var PayrollComponentRequester */
    private $payrollComponentRequester;
    /*** @var PayrollComponentRepository */
    private $payrollComponentRepository;
    /*** @var BusinessWeekendRepo */
    private $businessWeekRepo;
    /** * @var BusinessHolidayRepo */
    private $businessHolidayRepo;

    /**
     * PayrollController constructor.
     * @param PayrollSettingRepository $payroll_setting_repository
     * @param PayrollSettingRequester $payroll_setting_requester
     * @param PayrollSettingUpdater $payroll_setting_updater
     * @param PayrollComponentUpdater $payroll_component_updater
     * @param PayrollComponentRequester $payroll_component_requester
     * @param PayrollComponentRepository $payroll_component_repository
     * @param BusinessWeekendRepo $business_weekend_repo
     * @param BusinessHolidayRepo $business_holiday_repo
     */
    public function __construct(PayrollSettingRepository $payroll_setting_repository,
                                PayrollSettingRequester $payroll_setting_requester,
                                PayrollSettingUpdater $payroll_setting_updater,
                                PayrollComponentUpdater $payroll_component_updater,
                                PayrollComponentRequester $payroll_component_requester,
                                PayrollComponentRepository $payroll_component_repository,
                                BusinessWeekendRepo $business_weekend_repo,
                                BusinessHolidayRepo $business_holiday_repo)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        $this->payrollSettingRequester = $payroll_setting_requester;
        $this->payrollSettingUpdater = $payroll_setting_updater;
        $this->payrollComponentUpdater = $payroll_component_updater;
        $this->payrollComponentRequester = $payroll_component_requester;
        $this->payrollComponentRepository = $payroll_component_repository;
        $this->businessWeekRepo = $business_weekend_repo;
        $this->businessHolidayRepo = $business_holiday_repo;
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
            'pay_day_type' => 'required|in:' . implode(',', PayDayType::get()),
            'pay_day' => 'required_if:pay_day_type,fixed_date'
        ]);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        if (!$payroll_setting) return api_response($request, null, 404);
        $this->payrollSettingRequester->setIsEnable($request->is_enable)->setPayDayType($request->pay_day_type)->setPayDay($request->pay_day);
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

    public function addComponent($business, $payroll_setting, Request $request, AdditionCreator $addition_creator, DeductionsCreator $deduction_creator, Requester $package_requester, PackageCreator $package_creator, PackageUpdater $package_updater)
    {
        $this->validate($request, [
            'addition' => 'required',
            'deduction' => 'required',
        ]);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->setModifier($business_member->member);

        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        if (!$payroll_setting) return api_response($request, null, 404);

        $this->payrollComponentRequester->setSetting($payroll_setting)->setAddition($request->addition)->setDeduction($request->deduction)->setComponentDelete($request->component_delete_data);
        if ($this->payrollComponentRequester->checkError()) return api_response($request, null, 404, ['message' => 'Duplicate components found!']);

        DB::beginTransaction();
        $addition_creator->setPayrollComponentRequester($this->payrollComponentRequester)->createOrUpdate();
        $addition_creator->setPayrollComponentRequester($this->payrollComponentRequester)->delete();

        $deduction_creator->setPayrollComponentRequester($this->payrollComponentRequester)->createOrUpdate();
        $deduction_creator->setPayrollComponentRequester($this->payrollComponentRequester)->delete();

        $package_requester->setPackage($request->packages)->setPackageDelete($request->package_delete_data);
        $package_creator->setPayrollSetting($payroll_setting)->setPackageRequester($package_requester->getPackagesForAdd())->create();
        $package_updater->setPayrollSetting($payroll_setting)->setPackageRequester($package_requester->getPackagesForUpdate())->update();
        $package_updater->setPackageRequester($package_requester->getPackageDelete())->delete();
        DB::commit();
        return api_response($request, null, 200);
    }

    public function grossComponentAddUpdate($business, $payroll_setting, Request $request, Creator $creator, Updater $updater)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->setModifier($business_member->member);

        $payroll_setting = $this->payrollSettingRepository->find((int)$payroll_setting);
        if (!$payroll_setting) return api_response($request, null, 404);

        $this->payrollComponentRequester->setSetting($payroll_setting)->setGrossComponentAdd($request->added_data)->setGrossComponentUpdate($request->updated_data)->setGrossComponentDelete($request->delete_data);
        $creator->setPayrollComponentRequester($this->payrollComponentRequester)->create();
        $updater->setPayrollComponentRequester($this->payrollComponentRequester)->update();
        $updater->setPayrollComponentRequester($this->payrollComponentRequester)->delete();

        return api_response($request, null, 200);
    }

    public function getPayrollcomponents(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        $gross_payroll_components = $business->payrollSetting->components()->where('type', Type::GROSS)->where('target_type', TargetType::GENERAL)->where(function($query) {
             return $query->where('is_default', 1)->orWhere('is_active',1);
        })->orderBy('type')->get();

        $payroll_components = $business->payrollSetting->components()->where('type','<>',Type::GROSS)->orderBy('type')->get();


        $gross [] = [
            'id' => null,
            'name' => Type::GROSS,
            'title' => 'Gross Salary',
            'type' => null
        ];
        foreach ($gross_payroll_components as $gross_component) {
            if ($gross_component->type == Type::GROSS) array_push($gross, [
                'id' => $gross_component->id,
                'name' => $gross_component->name,
                'title' => $gross_component->is_default ? Components::getComponents($gross_component->name)['value'] : $gross_component->value,
                'type' => $gross_component->type
            ]);
        }
        $addition = $deduction = [];
        foreach ($payroll_components as $payroll_component) {
            if ($payroll_component->type == Type::ADDITION) array_push($addition, [
                'id' => $payroll_component->id,
                'name' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value,
                'type' => $payroll_component->type
            ]);
            if ($payroll_component->type == Type::DEDUCTION) array_push($deduction, [
                'id' => $payroll_component->id,
                'name' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value,
                'type' => $payroll_component->type
            ]);
        }
        return api_response($request, null, 200, ['payroll_components' => ['gross_component' => $gross, 'addition_component' => $addition, 'deduction_component' => $deduction]]);
    }

    public function checkPayDayConflicting(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        /** @var PayrollSetting $payroll_setting */
        $payroll_setting = $business->payrollSetting;
        $last_pay_day = $payroll_setting->last_pay_day;
        $next_pay_day = $payroll_setting->next_pay_day;
        $pay_day_type = $request->pay_day_type;
        $start_date_for_next_pay_day = Carbon::parse($next_pay_day)->subMonth();
        $start_date_for_new_pay_day = null;
        $end_date_for_new_pay_day = null;
        $new_pay_day = null;
        $type = '';
        if ($pay_day_type == PayDayType::FIXED_DATE){
            $new_pay_day = Carbon::now()->month(Carbon::parse($next_pay_day)->month)->day($request->pay_day);
            $start_date_for_new_pay_day = $new_pay_day->subMonth();
            $type = $start_date_for_next_pay_day < $start_date_for_new_pay_day ? 'GAP' : 'OVERLAPPING';
        }
        else if ($pay_day_type == PayDayType::LAST_WORKING_DAY){
            $new_pay_day = $this->lastWorkingDayOfMonth($business, Carbon::parse($next_pay_day)->lastOfMonth());
            $start_date_for_new_pay_day = $new_pay_day->subMonth();
            $type = $start_date_for_next_pay_day < $start_date_for_new_pay_day ? 'GAP' : 'OVERLAPPING';
        }
        $day_difference = $start_date_for_new_pay_day->diffInDays($start_date_for_next_pay_day);
        $pay_day_details = [
            'current_payroll_cycle' => $start_date_for_next_pay_day->format('jS').' 00:00:00 - '.Carbon::parse($next_pay_day)->subDay()->format('jS').' 23:59:59',
            'new_payroll_cycle' => $start_date_for_new_pay_day->format('jS').' 00:00:00 - '.$new_pay_day->subDay()->format('jS').' 23:59:59',
            'type' => $type,
            'days' => $day_difference
        ];

        return api_response($request, null, 200, ['pay_day_details' => $pay_day_details]);
    }


    private function lastWorkingDayOfMonth($business, $last_day_of_month)
    {
        while ($last_day_of_month) {
            if (!$this->businessWeekRepo->isWeekendByBusiness($business, $last_day_of_month) &&
                !$this->businessHolidayRepo->isHolidayByBusiness($business, $last_day_of_month)) break;
            $last_day_of_month = $last_day_of_month->subDay(1);
        }
        return $last_day_of_month;
    }

}
