<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\ModificationFields;

class Documents implements Arrayable
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
    /**
     * @var string
     */
    private $profile;
    private $basic_information;
    private $nominee;
    /**
     * @var PartnerLoanRequest
     */
    private $partnerLoanRequest;
    private $granter;

    public function __construct(Partner $partner, Resource $resource, PartnerLoanRequest $request = null)
    {
        $this->partner            = $partner;
        $this->resource           = $resource;
        $this->profile            = $resource->profile;
        $this->basic_information  = $this->partner->basicInformations;
        $this->partnerLoanRequest = $request;
        $this->setNominee();
        $this->setGranter();
    }

    /**
     * @return Documents
     */
    public function setNominee()
    {
        $this->nominee = $this->profile->nominee;
        return $this;
    }

    private function setGranter()
    {
        $this->granter = $this->profile->granter;
        return $this;
    }

    public function update() { }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->partnerLoanRequest ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    private function getDataFromLoanRequest() { }

    private function getDataFromProfile()
    {
        return [
            'picture'           => $this->profile->pro_pic,
            'nid_image'         => $this->profile->nid_image,
            'is_verified'       => $this->resource->is_verified,
            'nid_image_front'   => $this->profile->nid_image_front,
            'nid_image_back'    => $this->profile->nid_image_back,
            'nominee_document'  => [
                'picture'         => !empty($this->nominee) ? $this->nominee->pro_pic : null,
                'nid_front_image' => !empty($this->nominee) ? $this->nominee->nid_image_front : null,
                'nid_back_image'  => !empty($this->nominee) ? $this->nominee->nid_image_back : null,
            ],
            'grantor_document'  => [
                'picture'         => !empty($this->granter) ? $this->granter->pro_pic : null,
                'nid_front_image' => !empty($this->granter) ? $this->granter->nid_image_front : null,
                'nid_back_image'  => !empty($this->granter) ? $this->granter->nid_image_back : null,
            ],
            'business_document' => [
                'tin_certificate'          => $this->profile->tin_certificate,
                'trade_license_attachment' => !empty($this->bank_informations) ? $this->basic_information->trade_license_attachment : null,
                'statement'                => !empty($this->bank_informations) ? $this->bank_informations->statement : null
            ],
        ];
    }
}
