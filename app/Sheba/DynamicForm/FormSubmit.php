<?php

namespace App\Sheba\DynamicForm;

use App\Models\Partner;
use App\Models\PartnerBasicInformation;
use App\Models\Profile;
use Sheba\Dal\MefFields\Model as MefFields;

class FormSubmit
{
    /*** @var MefFields */
    private $fields;

    private $postData;

    /*** @var Partner */
    private $partner;

    /*** @var PartnerMefInformation */
    private $partnerMefInformation;

    private $partnerBasicInformation;

    /*** @var Profile */
    private $firstAdminProfile;

    /*** @var PartnerBasicInformation */
    private $basicInformation;

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
            $fieldData = (new FormField())->setFormInput(json_decode($field->data));
            if(($fieldData->data_source) !== "") {
                $source = $fieldData->data_source;
                $source_id = $fieldData->data_source_id;
                if(!isset($this->$source)) {
                    $setter = "set". ucfirst($source);
                    $this->$setter();
                }
                if(isset($this->postData[$source_id])) {
                    $this->$source->$source_id = trim($this->postData[$source_id]);
                }

            }
        }
        $this->storePartnerMefInformation();
    }


    public function setPartnerMefInformation()
    {
        if(isset($this->partner->partnerMefInformation->partner_information))
            $this->partnerMefInformation = (new PartnerMefInformation())
                ->setProperty(json_decode($this->partner->partnerMefInformation->partner_information, 1));
        else
            $this->partnerMefInformation = new PartnerMefInformation();
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
            $this->partner->partnerMefInformation->partner_information = json_encode($this->partnerMefInformation->getAvailable());
            $this->partner->partnerMefInformation->save();
        }
    }

    public function setPartnerBasicInformation()
    {
        $this->partnerBasicInformation = json_decode($this->partner->basicInformations->additional_information);
    }

    public function setFirstAdminProfile()
    {
        $this->firstAdminProfile = $this->partner->getFirstAdminResource()->profile;
    }

    public function setBasicInformation()
    {
        $this->basicInformation = $this->partner->basicInformations;
    }
}