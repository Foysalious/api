<?php

namespace Sheba\MerchantEnrollment;

use App\Models\Partner;
use App\Models\PartnerBankInformation;
use App\Models\PartnerBasicInformation;
use App\Models\Resource;

class PartnerAllInformation
{
    /** @var Partner $partner */
    protected $partner;
    /*** @var PartnerBasicInformation*/
    protected $partner_basic_information;
    /*** @var PartnerBankInformation */
    protected $partner_bank_information;
    /*** @var Resource */
    protected $resource;

    protected $additional_information;

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
        $this->partner_bank_information  = $this->partner->bankInformations()->first();
        return $this;
    }

    public function institution(): array
    {
        return $this->getFormFieldValues();
    }

    /**
     * @param $category_code = "institution" | "personal" | ""
     * @return mixed
     */
    public function getByCode($category_code)
    {
        return $this->$category_code();
    }

    private function getFormFieldValues(): array
    {
        $this->additional_information = (json_decode($this->partner_basic_information->additional_information));
        $values = [];
        foreach($this->formItems as $formItem) {
            if(isset($formItem['data_source']) && $formItem['data_source'] !== 'json') {
                if(isset($formItem['data_source_type']) && $formItem['data_source_type'] === "function")
                    $values[$formItem['id']] = $this->{$formItem['data_source']} ? $this->{$formItem['data_source']}->{$formItem['data_source_id']}() : '';
                else
                    $values[$formItem['id']] = $this->{$formItem['data_source']} ? $this->{$formItem['data_source']}->{$formItem['data_source_id']} : '';
            }
            elseif (isset($formItem['data_source']) && $formItem['data_source'] === 'json') {
                if(isset($this->additional_information))
                    $values[$formItem['id']] = $this->additional_information->{$formItem['id']} ?? '';

            }
        }
        return $values;
    }
}