<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\BusinessMember;
use Sheba\Dal\Salary\Salary;
use App\Models\Profile;

class CoWorkerGrossSalaryReportTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        /** @var Profile $profile */
        $profile = $business_member->profile();
        /** @var Salary $salary */
        $salary = $business_member->salary;

        return [
            'id' => $business_member->id,
            'profile' => [
                'name' => $profile->name,
                'email' => $profile->email,
            ],
            'gross_salary' => $salary ? $salary->gross_salary : null,
        ];
    }
}