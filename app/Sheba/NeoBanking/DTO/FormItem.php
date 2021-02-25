<?php


namespace Sheba\NeoBanking\DTO;


use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionException;

class FormItem implements Arrayable
{
    protected $field_type    = 'common';
    protected $title         = '';
    protected $input_type;
    protected $name          = '';
    protected $id            = '';
    protected $value         = '';
    protected $hint          = '';
    protected $error_message = '';
    protected $mandatory     = true;
    protected $is_editable   = true;
    protected $future_date   = false;
    protected $list_type;
    protected $views         = [];
    protected $list          = [];
    protected $check_list    = [];

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray()
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

    function setTitle($title): FormItem
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->field_type;
    }

    /**
     * @param string $field_type
     * @return FormItem
     */
    public function setFieldType($field_type)
    {
        $this->field_type = $field_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInputType()
    {
        return $this->input_type;
    }

    /**
     * @param mixed $input_type
     * @return FormItem
     */
    public function setInputType($input_type)
    {
        $this->input_type = $input_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return FormItem
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return FormItem
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * @param mixed $hint
     * @return FormItem
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @param mixed $error_message
     * @return FormItem
     */
    public function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param bool $mandatory
     * @return FormItem
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIsEditable()
    {
        return $this->is_editable;
    }

    /**
     * @param bool $is_editable
     * @return FormItem
     */
    public function setIsEditable($is_editable)
    {
        $this->is_editable = $is_editable;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getListType()
    {
        return $this->list_type;
    }

    /**
     * @param mixed $list_type
     * @return FormItem
     */
    public function setListType($list_type)
    {
        $this->list_type = $list_type;
        return $this;
    }

    /**
     * @return array
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param array $views
     * @return FormItem
     */
    public function setViews($views)
    {
        $this->views = $views;
        return $this;
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param array $list
     * @return FormItem
     */
    public function setList($list)
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @return array
     */
    public function getCheckList()
    {
        return $this->check_list;
    }

    /**
     * @param array $check_list
     * @return FormItem
     */
    public function setCheckList($check_list)
    {
        $this->check_list = $check_list;
        return $this;
    }

    public function setFormInput($input)
    {
        foreach ($input as $key => $value) {
            if (isset($this->$key)) $this->$key = $value;
        }
        return $this;
    }

}
