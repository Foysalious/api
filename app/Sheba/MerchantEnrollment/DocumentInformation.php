<?php

namespace Sheba\MerchantEnrollment;

class DocumentInformation extends PartnerAllInformation
{
    protected $first_admin_profile;

    private function document_get(): array
    {
        $this->setFirstAdminProfile();
        return $this->getFormFieldValues();
    }

    private function document_post($post_data)
    {
        $this->setFirstAdminProfile();
        $post_data = json_decode($post_data, 1);
        foreach ($this->formItems as $item) {
            if($item['input_type'] === 'header' || $item['is_editable'] === false) continue;
            if(isset($post_data[$item['id']])) {
                $key = $item['id'];
                if(isset($item['data_source']) && $item['data_source'] != 'json') {
                    if(isset($item['data_source_type']) && $item['data_source_type'] === 'function')
                        continue;
                    else
                        $this->{$item['data_source']}->{$item['data_source_id']} = $post_data[$key];

                }
            }
        }

        $this->partner_basic_information->save();
        $this->first_admin_profile->save();
    }

    /**
     * @param $category_code = "institution" | "personal" | ""
     * @return array
     */
    public function getByCode($category_code): array
    {
        return $this->document_get();
    }

    public function postByCode($category_code, $post_data)
    {
        $this->document_post($post_data);
    }

    private function setFirstAdminProfile()
    {
        $this->first_admin_profile = $this->partner->getFirstAdminResource()->profile;
    }
}