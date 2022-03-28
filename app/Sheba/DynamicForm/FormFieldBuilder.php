<?php

namespace App\Sheba\DynamicForm;

use App\Models\Partner;
use Sheba\Dal\PartnerMefInformation\Contract as PartnerMefInformationRepo;

class FormFieldBuilder
{
    private $field;

    private $partner;

    private $partnerMefInformation;

    /**
     * @param mixed $field
     * @return FormFieldBuilder
     */
    public function setField($field): FormFieldBuilder
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return FormFieldBuilder
     */
    public function setPartner(Partner $partner): FormFieldBuilder
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return FormFieldBuilder
     */
    public function setPartnerMefInformation(): FormFieldBuilder
    {
        if(!isset($this->partner->partnerMefInformation)) {
            $mefRepo = app(PartnerMefInformationRepo::class);
            $this->partner->partnerMefInformation = $mefRepo->create(["partner_id" => $this->partner->id]);
        }
        $this->partnerMefInformation = json_decode($this->partner->partnerMefInformation->partner_information);
        return $this;
    }

    public function build(): FormField
    {
        $form_field = (new FormField())->setFormInput(json_decode($this->field->data));
        if (($form_field->data_source) !== "") {
            $data_source = ($form_field->data_source);
            $data_source_id = ($form_field->data_source_id);
            if (!isset($this->$data_source)) {
                $function_name = "set" . ucfirst($data_source);
                $this->$function_name();
            }
            if (isset($this->$data_source)) {
                $data = $this->$data_source->$data_source_id ?? "";
                $form_field->setData($data);
            }
        }

        return $form_field;
    }

}