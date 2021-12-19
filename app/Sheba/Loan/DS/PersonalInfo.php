<?php namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use ReflectionException;
use Sheba\Dal\PartnerBankLoan\LoanTypes;
use Sheba\Loan\Completion;
use Sheba\Loan\Exceptions\EmailUsed;
use Sheba\ModificationFields;

class PersonalInfo implements Arrayable
{
    use ModificationFields;
    private $resource;
    private $profile;
    /** @var LoanRequestDetails|PartnerLoanRequest|null */
    private $loanDetails;
    private $partner;
    private $basic_information;

    /**
     * PersonalInfo constructor.
     * @param Partner $partner
     * @param Resource $resource
     * @param PartnerLoanRequest|null $request
     */
    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null)
    {
        $this->loanDetails = $request;
        if ($partner) {
            $this->partner           = $partner;
            $this->basic_information = $partner->basicInformations;
        }
        if ($resource) {
            $this->resource = $resource;
            $this->profile  = $resource->profile;
        }
    }

    public static function getValidators()
    {
        return [
            'gender'                          => 'string|in:Male,Female,Other',
            'birthday'                        => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
            'email'                           => 'email',
            'nid_issue_date'                  => 'sometimes|date|date_format:Y-m-d',
            #'father_name' => 'required_without:spouse_name',
            #'spouse_name' => 'required_without:father_name',
            'occupation'                      => 'string',
            'monthly_living_cost'             => 'numeric',
            'total_asset_amount'              => 'numeric',
            'monthly_loan_installment_amount' => 'sometimes|numeric'
        ];
    }

    /**
     * @param Request $request
     * @throws EmailUsed
     * @throws ReflectionException
     */
    public function update(Request $request, $loan_type = null)
    {
        if ($request->filled('email'))
            $this->validateEmail($request->email);
        $profile_data  = array_merge([
            'gender'         => $request->gender,
            'dob'            => $request->birthday,
            'birth_place'    => $request->birth_place,
            'occupation'     => $request->occupation,
            'email'          => $request->email,
            'nid_no'         => $request->nid_no,
            'nid_issue_date' => $request->nid_issue_date,
        ], (new Expenses($request->get('expenses')))->toArray());
        if(empty($request->email))
            $profile_data = array_except($profile_data,'email');
        $basic_data    = [
            'present_address'     => (new PresentAddress($request))->toString(),
            'permanent_address'   => (new PermanentAddress($request))->toString(),
            'other_id'            => $request->other_id,
            'other_id_issue_date' => $request->other_id_issue_date
        ];
        $resource_data = [
            'father_name' => $request->father_name,
            'spouse_name' => $request->spouse_name,
            'mother_name' => $request->mother_name,
        ];
        if ( $loan_type ===  LoanTypes::MICRO) {
            $profile_data = array_except($profile_data, ['gender', 'birth_place', 'occupation', 'email', 'nid_issue_date',
                'monthly_living_cost', 'total_asset_amount', 'monthly_loan_installment_amount']);
            $basic_data = array_except($basic_data, ['present_address', 'other_id', 'other_id_issue_date']);
            $resource_data = array_except($resource_data, ['spouse_name']);
        }
        $this->profile->update($this->withBothModificationFields($profile_data));
        $this->resource->update($this->withBothModificationFields($resource_data));
        $this->basic_information->update($this->withBothModificationFields($basic_data));
    }

    /**
     * @param $email
     * @return void
     * @throws EmailUsed
     */
    private function validateEmail($email)
    {
        if (empty($email))
            return;
        $exists = Profile::where('email', $email)->where('id', '<>', $this->profile->id)->first();
        if (!empty($exists))
            throw new EmailUsed();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function completion($loan_type = null)
    {
        $data = $this->toArray($loan_type);
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->partner->updated_at,
            $this->basic_information->updated_at
        ], ['net_worth']))->get();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray($loan_type = null)
    {
        return $this->loanDetails ? $this->dataFromLoanRequest() : $this->dataFromProfile($loan_type);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function dataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        if (isset($data['personal'])) {

            $data = $data['personal'];
        } elseif (($data = $data[0]) && isset($data['personal_info'])) {
            $data = $data['personal_info'];
        } else {
            $data = [];
        }
        $output = [];
        foreach (self::getKeys() as $key) {
            if ($key == 'permanent_address') {
                $output[$key] = (new PermanentAddress($data))->toArray();
            } elseif ($key == 'present_address') {
                $output[$key] = (new PresentAddress($data))->toArray();
            } elseif ($key == 'expenses') {
                $output[$key] = (new Expenses(array_key_exists($key, $data) ? (array)$data[$key] : []))->toArray();
            } elseif ($key == 'genders') {
                $output[$key] = constants('GENDER');
            } elseif ($key == 'occupation_lists') {
                $output[$key] = constants('SUGGESTED_OCCUPATION');
            } else {
                $output[$key] = array_key_exists($key, $data) ? $data[$key] : null;
            }
        }
        return $output;
    }

    public static function getKeys()
    {
        return [
            'name',
            'mobile',
            'gender',
            'email',
            'genders',
            'picture',
            'birthday',
            'present_address',
            'permanent_address',
            'is_same_address',
            'father_name',
            'spouse_name',
            'mother_name',
            'birth_place',
            'occupation',
            'occupation_lists',
            'expenses',
            'nid_no',
            'nid_issue_date',
            'other_id',
            'other_id_issue_date',
            'utility_bill_attachment',
            'net_worth'
        ];
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function dataFromProfile($loan_type)
    {
        $profile           = $this->profile;
        $permanent_address = (new PermanentAddress($this->basic_information))->toArray();
        $present_address   = (new PresentAddress($this->basic_information))->toArray();
        $data = [
            'name'                    => $profile->name,
            'mobile'                  => $profile->mobile,
            'birthday'                => $profile->dob,
            'nid_no'                  => $profile->nid_no,
            'father_name'             => $this->resource->father_name? $this->resource->father_name: null,
            'mother_name'             => $this->resource->mother_name,
            'permanent_address'       => $permanent_address,
        ];

        if(LoanTypes::MICRO === $loan_type) {
            return $data;
        }
        $otherData = [
            'gender'                  => $profile->gender,
            'email'                   => $profile->email,
            'genders'                 => constants('GENDER'),
            'picture'                 => $profile->pro_pic,
            'present_address'         => $present_address,
            'is_same_address'         => self::isSameAddress($present_address, $permanent_address),
            'spouse_name'             => $this->resource->spouse_name,
            'birth_place'             => $profile->birth_place,
            'occupation_lists'        => constants('SUGGESTED_OCCUPATION'),
            'occupation'              => $profile->occupation,
            'expenses'                => (new Expenses($profile->toArray()))->toArray(),
            'nid_issue_date'          => $profile->nid_issue_date,
            'other_id'                => $this->basic_information->other_id,
            'other_id_issue_date'     => $this->basic_information->other_id_issue_date,
            'utility_bill_attachment' => $profile->utility_bill_attachment
        ];

        return array_merge($data,$otherData);
    }

    public static function isSameAddress($present, $permanent)
    {
        if (empty($present) || empty($permanent))
            return false;
        foreach ($present as $key => $value) {
            if ($value != $permanent[$key])
                return false;
        }
        return true;
    }
}
