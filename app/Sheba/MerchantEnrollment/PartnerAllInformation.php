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

    protected function getFormFieldValues(): array
    {
        $this->additional_information = (json_decode($this->partner_basic_information->additional_information));
        $values = [];
        foreach($this->formItems as $formItem) {
            if(isset($formItem['data_source'])) {
                if($formItem['data_source'] !== 'json')
                    $this->mapNonJsonFormField($formItem, $values);
                else
                    $this->mapJsonFormField($formItem,$values);
            }
        }
        return $values;
    }

    public abstract function getByCode($category_code): array;

    public abstract function postByCode($category_code, $post_data);

    protected function mapNonJsonFormField($formItem, &$values)
    {
        if(isset($formItem['data_source_type']) && $formItem['data_source_type'] === "function")
            $values[$formItem['id']] = $this->{$formItem['data_source']} ? $this->{$formItem['data_source']}->{$formItem['data_source_id']}() : '';
        else
            $values[$formItem['id']] = $this->{$formItem['data_source']} ? $this->{$formItem['data_source']}->{$formItem['data_source_id']} : '';

    }

    protected function mapJsonFormField($formItem, &$values) {
        if(isset($this->additional_information))
            $values[$formItem['id']] = $this->additional_information->{$formItem['id']} ?? '';
    }
}