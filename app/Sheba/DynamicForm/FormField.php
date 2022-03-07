<?php

namespace App\Sheba\DynamicForm;

use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class FormField
{
    use ProtectedGetterTrait;

    protected $input_type = '';
    protected $label = '';
    protected $message = '';
    protected $hint = '';
    protected $id = '';
    protected $error = '';
    protected $data = '';
    protected $min_length = '';
    protected $max_length = '';
    protected $is_editable = false;
    protected $mandatory = false;

    public function setFormInput($input): FormField
    {
        foreach ($input as $key => $value)
            if (isset($this->$key)) $this->$key = $value;

        return $this;
    }

    /**
     * @param $value
     * @return FormField
     */
    public function setData($value): FormField
    {
        $this->data = $value;
        return $this;
    }
}