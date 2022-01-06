<?php

namespace Sheba\MerchantEnrollment;

use App\Models\Partner;
use App\Models\PartnerBasicInformation;
use App\Models\Resource;

class PartnerAllInformation
{
    /** @var Partner $partner */
    protected $partner;
    /*** @var PartnerBasicInformation*/
    protected $partner_basic_information;
    /*** @var Resource */
    protected $resource;

    private $formItems;

    /**
     * @param mixed $formItems
     * @return PartnerAllInformation
     */
    public function setFormItems($formItems): PartnerAllInformation
    {
        $this->formItems = $formItems;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return PartnerAllInformation
     */
    public function setPartner(Partner $partner): PartnerAllInformation
    {
        $this->partner = $partner;
        $this->partner_basic_information = $this->partner->basicInformations;
        return $this;
    }

    public function institution(): array
    {
        $values = [];
        foreach($this->formItems as $formItem) {
            if(isset($formItem['data_source']) && $formItem['data_source'] !== 'json') {
                if(isset($formItem['data_source_type']) && $formItem['data_source_type'] === "function")
                    $values[$formItem['id']] = $this->{$formItem['data_source']}->{$formItem['data_source_id']}();
                else
                    $values[$formItem['id']] = $this->{$formItem['data_source']}->{$formItem['data_source_id']};
            }
        }
        return $values;
    }

    /**
     * @param $category_code = "institution" | ""
     * @return mixed
     */
    public function getByCode($category_code)
    {
        return $this->$category_code();
    }
}