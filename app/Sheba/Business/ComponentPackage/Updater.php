<?php namespace App\Sheba\Business\ComponentPackage;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ComponentPackageTarget\ComponentPackageTargetRepository;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;
use Sheba\Dal\PayrollComponentPackage\ScheduleType;
use Sheba\Dal\PayrollSetting\PayrollSetting;

class Updater
{
    /** @var PayrollComponentPackageRepository */
    private $payrollComponentPackageRepository;
    /**@var ComponentPackageTargetRepository */
    private $componentPackageTargetRepository;
    private $packageRequester;
    /** @var PayrollSetting */
    private $payrollSetting;

    public function __construct()
    {
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
        $this->componentPackageTargetRepository = app(ComponentPackageTargetRepository::class);
    }

    public function setPayrollSetting(PayrollSetting $payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function setPackageRequester($package_requester)
    {
        $this->packageRequester = $package_requester;
        return $this;
    }

    public function update()
    {
        DB::transaction(function () {
            $this->makeData();
        });
    }

    public function delete()
    {
        if (!$this->packageRequester) return;
        foreach ($this->packageRequester as $package) {
            $existing_package = $this->payrollComponentPackageRepository->find($package);
            if (!$existing_package) continue;
            $this->payrollComponentPackageRepository->delete($existing_package);
        }
        return true;
    }

    private function makeData()
    {
        if(!$this->packageRequester) return;
        foreach ($this->packageRequester as $packages) {
            $existing_package = $this->payrollComponentPackageRepository->find($packages['id']);
            $data = [
                'payroll_component_id' => $existing_package->payroll_component_id,
                'key' => $packages['key'],
                'name' => $packages['name'],
                'is_active' => $packages['is_active'],
                'calculation_type' => $packages['calculation_type'],
                'is_percentage' => $packages['is_percentage'],
                'on_what' => $packages['on_what'],
                'amount' => $packages['amount'],
                'schedule_type' => $packages['schedule_type'],
                'periodic_schedule' => $packages['periodic_schedule'],
                'schedule_date' => $packages['schedule_date'],
            ];
            if (empty($existing_package->periodic_schedule_created_at) && empty($existing_package->generated_at)) {
                if ($packages['schedule_type'] == ScheduleType::PERIODICALLY) {
                    $package_generate_data = (new Formatter)->packageGenerateData($this->payrollSetting);
                    $data = array_merge($data, $package_generate_data);
                }
            }
            $this->payrollComponentPackageRepository->update($existing_package, $data);
            if (!empty($packages['effective_for'])) $this->makeTargetData($existing_package, $packages['effective_for'], $packages['target']);
        }
    }

    private function makeTargetData($package, $effective_for, $targets)
    {
        $existing_package_targets = $package->packageTargets;
        if ($existing_package_targets) {
            foreach ($existing_package_targets as $existing_target) {
                $this->componentPackageTargetRepository->delete($existing_target);
            }
        }
        $data = [];
        if (empty($targets)) {
            $this->componentPackageTargetRepository->create(['package_id' => $package->id, 'effective_for' => $effective_for]);
            return;
        }
        foreach ($targets as $target) {
            array_push($data, [
                'package_id' => $package->id,
                'effective_for' => $effective_for,
                'target_id' => $target
            ]);
        }
        $this->componentPackageTargetRepository->insert($data);
    }


}