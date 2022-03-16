<?php

namespace App\Sheba\DynamicForm;

use App\Sheba\DynamicForm\Exceptions\FormValidationException;
use Sheba\Dal\MefFields\Model as MefFields;

class FormValidator
{
    /*** @var MefFields */
    private $fields;

    private $postData;

    /**
     * @param mixed $fields
     * @return FormValidator
     */
    public function setFields($fields): FormValidator
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param mixed $postData
     * @return FormValidator
     */
    public function setPostData($postData): FormValidator
    {
        $this->postData = json_decode($postData, 1);
        return $this;
    }

    /**
     * @return void
     * @throws FormValidationException
     */
    public function validate()
    {
        foreach ($this->fields as $field) {
            $fieldData = json_decode($field->data);
            $form_field = (new FormField())->setFormInput($fieldData)->toArray();
            if($form_field['mandatory']) {
                if(!isset($this->postData[$fieldData['id']]))
                    throw new FormValidationException($form_field["error"]);
            }
            dd($form_field, $this->postData);
        }
    }
}