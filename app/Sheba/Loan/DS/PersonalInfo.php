<?php namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Sheba\Loan\Exceptions\EmailUsed;
use Sheba\ModificationFields;

class PersonalInfo implements Arrayable
{
    use ModificationFields;
    private $resource;
    private $profile;
    private $partnerLoanRequest;
    private $partner;
    private $basic_information;

    /**
     * PersonalInfo constructor.
     * @param Partner $partner
     * @param Resource $resource
     * @param PartnerLoanRequest|null $request
     */
    public function __construct(Partner $partner, Resource $resource, PartnerLoanRequest $request = null)
    {
        $this->resource           = $resource;
        $this->profile            = $resource->profile;
        $this->partnerLoanRequest = $request;
        $this->partner            = $partner;
        $this->basic_information  = $partner->basicInformations;
    }

    public static function getValidators()
    {
        return [
            'gender'                          => 'required|string|in:Male,Female,Other',
            'birthday'                             => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
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
     * @throws \ReflectionException
     */
    public function update(Request $request)
    {
        if ($request->has('email'))
            $this->validateEmail($request->email);
        $profile_data  = [
            'gender'                          => $request->gender,
            'dob'                             => $request->birthday,
            'birth_place'                     => $request->birth_place,
            'occupation'                      => $request->occupation,
            'email'                           => $request->email,
            'nid_no'                          => $request->nid_no,
            'nid_issue_date'                  => $request->nid_issue_date,
            'total_asset_amount'              => $request->total_asset_amount,
            'monthly_loan_installment_amount' => $request->monthly_loan_installment_amount,
            'monthly_living_cost'             => $request->monthly_living_cost,
        ];
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
        if (empty($email)) return ;
        $exists = Profile::where('email', $email)->where('id', '<>', $this->profile->id)->first();
        if (!empty($exists))
            throw new EmailUsed();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        return $this->partnerLoanRequest ? $this->dataFromLoanRequest() : $this->dataFromProfile();
    }

    /**
     * @return array
     */
    private function dataFromLoanRequest()
    {
        return [];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function dataFromProfile()
    {
        $profile = $this->profile;
        return [
            'name'                => $profile->name,
            'mobile'              => $profile->mobile,
            'gender'              => $profile->gender,
            'email'               => $profile->email,
            'genders'             => constants('GENDER'),
            'picture'             => $profile->pro_pic,
            'birthday'            => $profile->dob,
            'present_address'     => (new PresentAddress($this->basic_information))->toArray(),
            'permanent_address'   => (new PermanentAddress($this->basic_information))->toArray(),
            'father_name'         => $this->resource->father_name,
            'spouse_name'         => $this->resource->spouse_name,
            'mother_name'         => $this->resource->mother_name,
            'birth_place'         => $profile->birth_place,
            'occupation_lists'    => constants('SUGGESTED_OCCUPATION'),
            'occupation'          => $profile->occupation,
            'expenses'            => (new Expenses($profile))->toArray(),
            'nid_no'              => $profile->nid_no,
            'nid_issue_date'      => $profile->nid_issue_date,
            'other_id'            => $this->basic_information->other_id,
            'other_id_issue_date' => $this->basic_information->other_id_issue_date
        ];
    }
}
