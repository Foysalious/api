<?php namespace App\Sheba\Business\ComponentPackage;

use App\Models\BusinessMember;
use Sheba\Dal\PayrollComponentPackage\TargetType;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class Formatter
{
    private $businessMember;
    private $department;

    public function __construct()
    {
        $this->businessMember = app(BusinessMember::class);
        $this->department = app(DepartmentRepositoryInterface::class);
    }

    public function makePackageData($component)
    {
        $component_packages = $component->componentPackages;
        $data = [];
        foreach ($component_packages as $packages) {
            $targets = $packages->packageTargets;
            array_push($data, [
                'id' => $packages->id,
                'package_key' => $packages->key,
                'package_name' => $packages->name,
                'is_active' => $packages->is_active,
                'is_taxable' => $packages->is_taxable,
                'calculation_type' => $packages->calculation_type,
                'is_percentage' => (float)$packages->is_percentage,
                'on_what' => $packages->on_what,
                'amount' => $packages->amount,
                'schedule_type' => $packages->schedule_type,
                'periodic_schedule' => $packages->periodic_schedule,
                'schedule_date' => $packages->schedule_date,
                'target' => $this->getTarget($targets)
            ]);
        }
        return $data;
    }

    private function getTarget($targets)
    {
        $data = [];
        foreach ($targets as $target) {
            $data['effective_for'] = $target->effective_for;
            if ($target->effective_for == TargetType::GENERAL) continue;
            $data['selected'][] = [
                'target_id' => $target->target_id,
                'name' => $this->getTargetDetails($target->effective_for, $target->target_id)['name']
            ];
        }
        return $data;
    }

    private function getTargetDetails($type, $target_id)
    {
        if ($type == TargetType::EMPLOYEE) $target =  $this->businessMember->find($target_id)->profile();
        if($type == TargetType::DEPARTMENT) $target = $this->department->find($target_id);
        return [
            'name' => $target->name
        ];
    }
}