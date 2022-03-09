<?php

namespace App\Sheba\DynamicForm;

use App\Models\Partner;

class FormFieldBuilder
{
    private $field;

    private $partner;

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

    public function build(): FormField
    {
        $field_data = json_decode($this->field->data);
        $data_source = ($field_data->data_source);
        $data_source_id = ($field_data->data_source_id);
        if(!isset($this->$data_source)) {
            $function_name = "set".$data_source;
            $this->$function_name();
        }
        $data = $this->$data_source->$data_source_id;
        return (new FormField())->setFormInput($field_data)->setData($data);
    }

}