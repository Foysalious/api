<?php

namespace Sheba\MerchantEnrollment;

use App\Models\Partner;
use App\Models\PartnerBankInformation;
use App\Models\PartnerBasicInformation;
use App\Models\Resource;

abstract class PartnerAllInformation
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

    protected $formItems;

    protected $partner_resource_profile;

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
        if($operation_resource = $this->partner->operationResources()->first())
            $this->partner_resource_profile = $operation_resource->profile;
        else
            $this->partner_resource_profile =  $this->partner->admins()->first()->profile;
        return $this;
    }

    protected function getFormFieldValues(): array
    {
        $values = [];
        foreach($this->formItems as $formItem) {
            if(isset($formItem['data_source'])) {
                if(isset($formItem['data_source_type']) && $formItem['data_source_type'] === "function")
                    $values[$formItem['id']] = $this->{$formItem['data_source']} ? $this->{$formItem['data_source']}->{$formItem['data_source_id']}() : '';
                else
                    $values[$formItem['id']] = $this->{$formItem['data_source']} ? $this->{$formItem['data_source']}->{$formItem['data_source_id']} : '';
            }

        }
        return $values;
    }

    public abstract function getByCode($category_code): array;

    public abstract function postByCode($category_code, $post_data);
}