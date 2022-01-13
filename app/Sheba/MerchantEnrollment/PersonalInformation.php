<?php

namespace App\Sheba\MerchantEnrollment;

use Sheba\MerchantEnrollment\PartnerAllInformation;
use Sheba\ModificationFields;

class PersonalInformation extends PartnerAllInformation
{
    use ModificationFields;

    public function personal_get(): array
    {
        return $this->getFormFieldValues();
    }

    public function personal_post($post_data)
    {
        $post_data = json_decode($post_data,true);
        foreach ($this->formItems as $item) {
            if ($item['is_editable']) {
                if (isset($post_data[$item['id']])) {
                    $key = $item['id'];
                    if (isset($item['data_source'])){
                        $this->{$item['data_source']}->{$item['data_source_id']} = $post_data[$key];
                    }
                }
            }
        }
        $this->partner_resource_profile->save();
    }

    /**
     * @param $category_code = "institution" | "personal" | ""
     * @return array
     */
    public function getByCode($category_code): array
    {
        return $this->personal_get();
    }

    public function postByCode($category_code, $post_data)
    {
        $this->personal_post($post_data);
    }


}