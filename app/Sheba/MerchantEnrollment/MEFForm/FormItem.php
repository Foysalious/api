<?php

namespace Sheba\MerchantEnrollment\MEFForm;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;

class FormItem implements Arrayable
{
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

    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $data       = [];
        foreach ($reflection->getProperties() as $item) {
            if (!$item->isProtected())
                continue;
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }


    public function setFormInput($input): FormItem
    {
        foreach ($input as $key => $value)
            if (isset($this->$key)) $this->$key = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setData($value): FormItem
    {
        $this->data = $value;
        return $this;
    }
}