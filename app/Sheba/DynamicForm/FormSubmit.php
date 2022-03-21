<?php

namespace App\Sheba\DynamicForm;

use App\Models\Partner;
use Sheba\Dal\MefFields\Model as MefFields;

class FormSubmit
{
    /*** @var MefFields */
    private $fields;

    private $postData;

    /*** @var Partner */
    private $partner;

    private $partnerMefInformation;

    /**
     * @param mixed $fields
     * @return FormSubmit
     */
    public function setFields($fields): FormSubmit
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param mixed $postData
     * @return FormSubmit
     */
    public function setPostData($postData): FormSubmit
    {
        $this->postData = json_decode($postData,1);
        return $this;
    }

    /**
     * @return void
     */
    public function store()
    {
        foreach ($this->fields as $field) {
            $fieldData = json_decode($field->data);
            if(isset($fieldData->data_source)) {
                $source = $fieldData->data_source;
                $source_id = $fieldData->data_source_id;
                if(!isset($this->$source)) {
                    $setter = "set". ucfirst($source);
                    $this->$setter();
                }
                if(isset($this->postData[$source_id]))
                    $this->$source[$source_id] = trim($this->postData[$source_id]);

            }
        }
        $this->storePartnerMefInformation();
    }

    public function setPartnerMefInformation()
    {
        $this->partnerMefInformation = json_decode($this->partner->partnerMefInformation->partner_information,1) ? : [];
    }

    /**
     * @param Partner $partner
     * @return FormSubmit
     */
    public function setPartner(Partner $partner): FormSubmit
    {
        $this->partner = $partner;
        return $this;
    }

    private function storePartnerMefInformation()
    {
        if(isset($this->partnerMefInformation)) {
            $this->partner->partnerMefInformation->partner_information = json_encode($this->partnerMefInformation);
            $this->partner->partnerMefInformation->save();
        }
    }
}