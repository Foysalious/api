<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
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
    private $partnerLoanRequest;

    public function __construct(Partner $partner, Resource $resource, PartnerLoanRequest $request = null)
    {
        $this->partner            = $partner;
        $this->resource           = $resource;
        $this->profile            = $resource->profile;
        $this->partnerLoanRequest = $request;
        $this->setGranter();
        $this->setNominee();
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
        if(empty($nominee)){
          $nominee=(new NomineeInfo(['name'=>$request->nominee_name,'mobile'=>$request->nominee_mobile]))->create($this->partner);
        }
        if (empty($granter)){
            $granter=(new GranterInfo(['name'=>$request->grantor_name,'mobile'=>$request->grantor_mobile]))->create($this->partner);
        }
        $this->profile->update($this->withBothModificationFields(['nominee_id'=>$nominee->id,'nominee_relation'=>$request->nominee_relation,'grantor_id'=>$granter->id,'grantor_relation'=>$request->grantor_relation]));
    }

    /**
     * @return array|void
     * @throws \ReflectionException
     */
    public function toArray()
    {
        return $this->partnerLoanRequest ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    private function getDataFromLoanRequest() { }

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
