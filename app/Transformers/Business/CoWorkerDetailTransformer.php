<?php namespace App\Transformers\Business;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use App\Sheba\Business\SalaryLog\Formatter as SalaryLogFormatter;
use Sheba\Dal\SalaryLog\SalaryLogRepository;
use Sheba\Dal\PayrollComponent\Components;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Salary\SalaryRepository;
use App\Models\BusinessMember;
use App\Models\Business;
use App\Models\Member;

class CoWorkerDetailTransformer extends TransformerAbstract
{
    const THRESHOLD = 17;

    private $business;
    private $isInactiveFilterApplied;
    private $SalaryRepository;
    private $SalaryLogRepository;

    public function __construct(Business $business, $is_inactive_filter_applied)
    {
        $this->business = $business;
        $this->isInactiveFilterApplied = $is_inactive_filter_applied;
        $this->SalaryRepository = app(SalaryRepository::class);
        $this->SalaryLogRepository = app(SalaryLogRepository::class);
    }

    /**
     * @param Member $member
     * @return array
     */
    public function transform(Member $member)
    {
        $business_member = ($this->isInactiveFilterApplied) ? $member->businessMemberGenerated : $member->businessMember;

        return [
            'basic_info' => $this->getBasicInfo($member, $business_member),
            'official_info' => $this->getOfficialInfo($business_member),
            'personal_info' => $this->getPersonalInfo($member, $business_member),
            'financial_info' => $this->getFinancialInfo($member),
            'emergency_info' => $this->getEmergencyInfo($member),
            'salary_info' => $this->getSalaryInfo($business_member),
            'profile_completion' => $this->profileCompletion($member, $business_member),
        ];
    }

    private function getBasicInfo($member, $business_member)
    {
        $profile = $member->profile;
        $role = $business_member ? $business_member->role : null;
        $department = $role ? $role->businessDepartment : null;
        $department_name = $department ? $department->name : null;
        $department_id = $department ? $department->id : null;
        $designation = $role ? $role->name : null;

        $count = 0;
        if ($profile->name) $count++;
        if ($profile->email) $count++;
        if ($department) $count++;
        if ($designation) $count++;
        $basic_info_completion = round((($count / 4) * self::THRESHOLD), 0);
        return [
            'id' => $member->id,
            'status' => $business_member->status,
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'profile_picture_name' => $profile->pro_pic ? array_last(explode('/', $profile->pro_pic)) : null,
                'profile_picture' => $profile->pro_pic,
                'email' => $profile->email,
            ],
            'department' => $department_name,
            'department_id' => $department_id,
            'designation' => $designation,
            'manager_detail' => $business_member->manager_id ? $this->getManagerDetails($business_member->manager_id) : null,
            'basic_info_completion' => $basic_info_completion,
        ];
    }

    private function getOfficialInfo($business_member)
    {
        $count = 0;
        if ($business_member->employee_id ||
            $business_member->join_date ||
            $business_member->grade ||
            $business_member->employee_type ||
            $business_member->previous_institution) $count++;
        $official_info_completion = round((($count / 1) * self::THRESHOLD), 0);

        return [
            'employee_id' => $business_member->employee_id,
            'join_date' => $business_member->join_date,
            'grade' => $business_member->grade,
            'employee_type' => $business_member->employee_type,
            'previous_institution' => $business_member->previous_institution,
            'official_info_completion' => $official_info_completion
        ];
    }

    private function getPersonalInfo($member, $business_member)
    {
        $profile = $member->profile;
        $count = 0;
        if ($business_member->mobile ||
            $profile->dob ||
            $profile->address ||
            $profile->nationality ||
            $profile->nid_no ||
            $profile->nationality ||
            $profile->nid_image_front ||
            $profile->nid_image_back) $count++;

        $personal_info_completion = round((($count / 1) * self::THRESHOLD), 0);

        return [
            'mobile' => $business_member->mobile,
            'date_of_birth' => $profile->dob,
            'address' => $profile->address,
            'nationality' => $profile->nationality,
            'nid_no' => $profile->nid_no,
            'profile_id' => $profile->id,
            'nid_image_front_name' => $profile->nid_image_front ? array_last(explode('/', $profile->nid_image_front)) : null,
            'nid_image_front' => $profile->nid_image_front,
            'nid_image_back_name' => $profile->nid_image_back ? array_last(explode('/', $profile->nid_image_back)) : null,
            'nid_image_back' => $profile->nid_image_back,
            'personal_info_completion' => $personal_info_completion
        ];
    }

    private function getFinancialInfo($member)
    {
        $profile = $member->profile;
        $profile_bank_info = $profile->banks->last();

        $bank_name = $profile_bank_info ? ucwords(str_replace('_', ' ', $profile_bank_info->bank_name)) : null;
        $account_no = $profile_bank_info ? $profile_bank_info->account_no : null;

        $count = 0;
        if ($profile->tin_no ||
            $profile->tin_certificate ||
            $bank_name ||
            $account_no) $count++;

        $financial_info_completion = round((($count / 1) * self::THRESHOLD), 0);

        return [
            'tin_no' => $profile->tin_no,
            'tin_certificate_name' => $profile->tin_certificate ? array_last(explode('/', $profile->tin_certificate)) : null,
            'tin_certificate' => $profile->tin_certificate,
            'bank_name' => $bank_name,
            'account_no' => $account_no,
            'financial_info_completion' => $financial_info_completion
        ];
    }

    private function getEmergencyInfo($member)
    {
        $count = 0;
        if ($member->emergency_contract_person_name ||
            $member->emergency_contract_person_number ||
            $member->emergency_contract_person_relationship) $count++;

        $emergency_info_completion = round((($count / 1) * self::THRESHOLD), 0);

        return [
            'emergency_contract_person_name' => $member->emergency_contract_person_name,
            'emergency_contract_person_number' => $member->emergency_contract_person_number,
            'emergency_contract_person_relationship' => $member->emergency_contract_person_relationship,
            'emergency_info_completion' => $emergency_info_completion
        ];
    }

    private function getSalaryInfo($business_member)
    {
        $payroll_setting = $this->business->payrollSetting;
        $payroll_percentage_breakdown = (new GrossSalaryBreakdownCalculate())->componentPercentageBreakdown($payroll_setting);

        $count = 0;
        $salary = $business_member->salary;
        if ($salary && $salary->gross_salary) $count++;
        $salary_completion = round((($count / 1) * self::THRESHOLD), 0);

        $gross_salary_breakdown[Components::BASIC_SALARY] = $payroll_percentage_breakdown->basicSalary;
        $gross_salary_breakdown[Components::HOUSE_RENT] = $payroll_percentage_breakdown->houseRent;
        $gross_salary_breakdown[Components::MEDICAL_ALLOWANCE] = $payroll_percentage_breakdown->medicalAllowance;
        $gross_salary_breakdown[Components::CONVEYANCE] = $payroll_percentage_breakdown->conveyance;

        $gross_salary_breakdown['gross_salary'] = $salary ? floatValFormat($salary->gross_salary) : null;
        $gross_salary_breakdown['gross_salary_log'] = $this->getSalaryLog($business_member);
        $gross_salary_breakdown['gross_salary_completion'] = $salary_completion;

        return $gross_salary_breakdown;
    }

    private function profileCompletion($member, $business_member)
    {
        $count = 0;
        $basic_info_completion = $this->getBasicInfo($member, $business_member)['basic_info_completion'];
        $official_info_completion = $this->getOfficialInfo($business_member)['official_info_completion'];
        $personal_info_completion = $this->getPersonalInfo($member, $business_member)['personal_info_completion'];
        $financial_info_completion = $this->getFinancialInfo($member)['financial_info_completion'];
        $emergency_info_completion = $this->getEmergencyInfo($member)['emergency_info_completion'];
        $gross_salary_info_completion = $this->getSalaryInfo($business_member)['gross_salary_completion'];

        if ($basic_info_completion) $count++;
        if ($official_info_completion) $count++;
        if ($personal_info_completion) $count++;
        if ($financial_info_completion) $count++;
        if ($emergency_info_completion) $count++;
        if ($gross_salary_info_completion) $count++;

        return round((($count / 6) * 100), 0);
    }

    private function getManagerDetails($manager_id)
    {
        $manager_business_member = BusinessMember::findOrFail($manager_id);
        $manager_member = $manager_business_member->member;
        $manager_profile = $manager_member->profile;
        $role = $manager_business_member->role;

        return [
            'id' => $manager_member->id,
            'business_member' => $manager_business_member->id,
            'name' => $manager_profile->name,
            'employee_id' => $manager_business_member->employee_id,
            'profile' => [
                'id' => $manager_profile->id,
                'name' => $manager_profile->name,
                'pro_pic' => $manager_profile->pro_pic,
                'mobile' => $manager_business_member->mobile,
                'email' => $manager_profile->email
            ],
            'status' => $manager_business_member->status,
            'department_id' => $role ? $role->businessDepartment->id : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'designation' => $role ? $role->name : null
        ];
    }

    private function getSalaryLog($business_member)
    {
        $salary = $business_member->salary;
        if (!$salary) return [];
        $salary_logs = $salary->logs()->orderBy('created_at', 'DESC')->get();
        return (new SalaryLogFormatter())->setSalaryLogs($salary_logs)->format();
    }
}
