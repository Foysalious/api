<?php namespace Sheba\NeoBanking\DTO\Inputs;


use Sheba\NeoBanking\DTO\FormItem;

class EditText extends FormItem
{
    protected $field_type='editText';
    protected $inputType;
    protected $name;
    protected $id;
    protected $value;
    protected $hint;
    protected $error_message;
    protected $mandatory;
    protected $is_editable;

    /**
     * @param string $field_type
     * @return EditText
     */
    public function setFieldType($field_type)
    {
        $this->field_type = $field_type;
        return $this;
    }

    /**
     * @param mixed $inputType
     * @return EditText
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
        return $this;
    }

    /**
     * @param mixed $name
     * @return EditText
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $id
     * @return EditText
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $value
     * @return EditText
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param mixed $hint
     * @return EditText
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * @param mixed $error_message
     * @return EditText
     */
    public function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
        return $this;
    }

    /**
     * @param mixed $mandatory
     * @return EditText
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    /**
     * @param mixed $is_editable
     * @return EditText
     */
    public function setIsEditable($is_editable)
    {
        $this->is_editable = $is_editable;
        return $this;
    }

}
