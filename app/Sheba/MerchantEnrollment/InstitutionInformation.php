<?php

namespace Sheba\MerchantEnrollment;

use App\Models\PartnerBankInformation;
use Sheba\ModificationFields;

class InstitutionInformation extends PartnerAllInformation
{
    use ModificationFields;

    public function institution_get(): array
    {
        return $this->getFormFieldValues();
    }

    public function institution_post($post_data)
    {
        $this->additional_information = (json_decode($this->partner_basic_information->additional_information, 1));
        if(!isset($this->partner_bank_information)) $this->createDefaultPartnerBankInfo();
        $json_data = array();
        $post_data = json_decode($post_data, 1);
        foreach ($this->formItems as $item) {
            if($item['input_type'] !== 'header') {
                if(isset($post_data[$item['id']])) {
                    $key = $item['id'];
                    if(isset($item['data_source']) && $item['data_source'] != 'json') {
                        if(isset($item['data_source_type']) && $item['data_source_type'] === 'function')
                            continue;
                        else
                            $this->{$item['data_source']}->{$item['data_source_id']} = $post_data[$key];
                    } else
                        $json_data[$key] = $post_data[$key];

                }
            }
        }
        $data = ($this->additional_information && count($this->additional_information)) ?
            json_encode(array_merge($this->additional_information, $json_data)) : json_encode($json_data);

        $this->partner->save();
        $this->partner_basic_information->additional_information = $data;
        $this->partner_basic_information->save();
        $this->partner_bank_information->save();
    }

    /**
     * @param $category_code = "institution" | "personal" | ""
     * @return array
     */
    public function getByCode($category_code): array
    {
        return $this->institution_get();
    }

    public function postByCode($category_code, $post_data)
    {
        $this->institution_post($post_data);
    }

    private function createDefaultPartnerBankInfo()
    {
        $this->setModifier($this->partner);
        $this->partner_bank_information = PartnerBankInformation::create($this->withBothModificationFields(["partner_id" => $this->partner->id]));
    }
}