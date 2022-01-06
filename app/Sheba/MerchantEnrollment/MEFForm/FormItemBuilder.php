<?php

namespace Sheba\MerchantEnrollment\MEFForm;

use ReflectionException;

class FormItemBuilder
{
    private $data;
    private $item;

    /**
     * @param mixed $data
     * @return FormItemBuilder
     */
    public function setData($data): FormItemBuilder
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param mixed $item
     * @return FormItemBuilder
     */
    public function setItem($item): FormItemBuilder
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @param $item
     * @return mixed
     */
    public function build($item)
    {
        $this->item = $item;
        $type       = $this->item['input_type'];
        return $this->$type();
    }

    private function text(): array
    {
        return $this->initItem()->toArray();
    }

    private function dropdown(): array
    {
        return $this->initItem()->toArray();
    }

    private function header(): array
    {
        return $this->initItem()->toArray();
    }

    private function phone(): array
    {
        return $this->initItem()->toArray();
    }

    private function date_picker(): array
    {
        return $this->initItem()->toArray();
    }

    /**
     * @param $set
     * @return FormItem
     */
    private function initItem($set = true): FormItem
    {
        $item = (new FormItem())->setFormInput($this->item);
        if ($set) $this->setValue($item);
        return $item;
    }

    /**
     * @param $item
     * @return void
     */
    private function setValue(&$item)
    {
        if (!array_key_exists('id', $this->item)) return;

        if (isset($this->data[$this->item['id']])) $item->setData($this->data[$this->item['id']]);
    }
}