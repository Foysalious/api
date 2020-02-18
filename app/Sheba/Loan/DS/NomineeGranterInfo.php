<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Sheba\Loan\Completion;
use Sheba\ModificationFields;

class NomineeGranterInfo implements Arrayable
{
    use ModificationFields;
    /**
     * @var Partner
     */
    private $partner;
    /**
     * @var Resource
     */
    private $resource;
    private $profile;
    /** @var Profile $nominee */
    private $nominee;
    /** @var Profile $granter */
    private $granter;
    /**
     * @var PartnerLoanRequest
     */
    private $loanDetails;

    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null)
    {
        $this->partner     = $partner;
        $this->resource    = $resource;
        $this->loanDetails = $request;
        if ($this->resource) {
            $this->profile = $resource->profile;
            $this->setGranter();
            $this->setNominee();
        }
    }

    /**
     * @return NomineeGranterInfo
     */
    private function setGranter()
    {
        $this->granter = $this->profile->granter;
        return $this;
    }

    public static function getValidator()
    {
        return [
            'nominee_name'     => 'required|string',
            'nominee_mobile'   => 'required|string|mobile:bd',
            'nominee_relation' => 'required|string',
            'grantor_name'     => 'required|string',
            'grantor_mobile'   => 'required|string|mobile:bd',
            'grantor_relation' => 'required|string'
        ];
    }

    /**
     * @return mixed
     */
    public function getNominee()
    {
        return $this->nominee;
    }

    /**
     * @return NomineeGranterInfo
     */
    public function setNominee()
    {
        $this->nominee = $this->profile->nominee;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGranter()
    {
        return $this->granter;
    }

    /**
     * @param Request $request
     * @throws \ReflectionException
     */
    public function update(Request $request)
    {
        $nominee = Profile::where('mobile', formatMobile($request->nominee_mobile))->first();
        $granter = Profile::where('mobile', formatMobile($request->grantor_mobile))->first();
        if (empty($nominee)) {
            $nominee = (new NomineeInfo([
                'name'   => $request->nominee_name,
                'mobile' => $request->nominee_mobile
            ]))->create($this->partner);
        }
        if (empty($granter)) {
            $granter = (new GranterInfo([
                'name'   => $request->grantor_name,
                'mobile' => $request->grantor_mobile
            ]))->create($this->partner);
        }
        $this->profile->update($this->withBothModificationFields([
            'nominee_id'       => $nominee->id,
            'nominee_relation' => $request->nominee_relation,
            'grantor_id'       => $granter->id,
            'grantor_relation' => $request->grantor_relation
        ]));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function completion()
    {
        $data = $this->toArray();
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->granter ? $this->granter->updated_at : null,
            $this->nominee ? $this->nominee->updated_at : null
        ], [
            'address',
            'dob',
            'occupation',
            'net_worth'
        ]))->get();
    }

    /**
     * @return array|void
     * @throws \ReflectionException
     */
    public function toArray()
    {
        return $this->loanDetails ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    private function getDataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        if (isset($data['nominee_granter'])) {

            $data = $data['nominee_granter'];
        } elseif (($data = $data[0]) && isset($data['nominee_grantor_info'])) {
            $data = $data['nominee_grantor_info'];
        } else {
            $data = [];
        }
        return [
            'grantor' => array_merge((new GranterInfo((array_key_exists('grantor', $data) ? $data['grantor'] : null)))->toArray(), ['grantor_relation' => (array_key_exists('grantor', $data) ? $data['grantor']['grantor_relation'] : null)]),
            'nominee' => array_merge((new GranterInfo((array_key_exists('nominee', $data) ? $data['nominee'] : null)))->toArray(), ['nominee_relation' => (array_key_exists('nominee', $data) ? $data['nominee']['nominee_relation'] : null)])
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getDataFromProfile()
    {
        return [
            'grantor' => array_merge((new GranterInfo($this->granter ? $this->granter->toArray() : []))->toArray(), ['grantor_relation' => $this->profile->grantor_relation]),
            'nominee' => array_merge((new NomineeInfo($this->nominee ? $this->nominee->toArray() : []))->toArray(), ['nominee_relation' => $this->profile->nominee_relation])
        ];
    }
}
