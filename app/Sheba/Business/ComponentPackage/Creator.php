<?php namespace App\Sheba\Business\ComponentPackage;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\ComponentPackageTarget\ComponentPackageTargetRepository;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;

class Creator
{
    private $packageRequester;
    /** @var PayrollComponentRepository */
    private $payrollComponentRepository;
    /*** @var PayrollComponentPackageRepository */
    private $payrollComponentPackageRepository;
    private $manager;
    /** @var ComponentPackageTargetRepository*/
    private $componentPackageTargetRepository;

    public function __construct()
    {
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
        $this->componentPackageTargetRepository = app(ComponentPackageTargetRepository::class);
    }

    public function setPackageRequester($package_requester)
    {
        $this->packageRequester = $package_requester;
        return $this;
    }

    public function create()
    {
        DB::transaction(function () {
            $this->makeData();
        });
    }

    private function makeData()
    {
        if(!$this->packageRequester) return;
        foreach ($this->packageRequester as $packages) {
            $component = $this->payrollComponentRepository->where('name', $packages['component'])->first();
            foreach ($packages['package'] as $package) {
                $data = [
                    'payroll_component_id' => $component->id,
                    'key' => $package['key'],
                    'name' => $package['name'],
                    'is_active' => $package['is_active'],
                    'calculation_type' => $package['calculation_type'],
                    'is_percentage' => $package['is_percentage'],
                    'on_what' => $package['on_what'],
                    'amount' => $package['amount'],
                    'schedule_type' => $package['schedule_type'],
                    'periodic_schedule' => $package['periodic_schedule'],
                    'schedule_date' => $package['schedule_date'],
                ];
                $new_package = $this->payrollComponentPackageRepository->create($data);
                $this->makeTargetData($new_package->id, $package['effective_for'], $package['target']);
            }
        }
    }

    private function makeTargetData($package_id, $effective_for, $targets)
    {
        $data = [];
        if (empty($targets)) {
            $this->componentPackageTargetRepository->create(['package_id' => $package_id, 'effective_for' => $effective_for]);
            return;
        }
        foreach ($targets as $target) {
            array_push($data, [
                'package_id' => $package_id,
                'effective_for' => $effective_for,
                'target_id' => $target
            ]);
        }
        $this->componentPackageTargetRepository->insert($data);
    }
}