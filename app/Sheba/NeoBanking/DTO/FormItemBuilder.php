<?php


namespace Sheba\NeoBanking\DTO;


use ReflectionException;

class FormItemBuilder
{
    private $data;
    private $item;

    /**
     * @param mixed $data
     * @return FormItemBuilder
     */
    public function setData($data)
    {
        $this->data = (array)$data;
        return $this;
    }

    private function setValue(&$item)
    {
        if (!array_key_exists('name', $this->item)) return $item;
        if (isset($this->data[$this->item['name']])) $item->setValue($this->data[$this->item['name']]);
        return $item;
    }

    private function initItem($set = true)
    {
        $item = (new FormItem())->setFormInput($this->item);
        if ($set) $this->setValue($item);
        return $item;
    }

    /**
     * @param $item
     * @return mixed
     */
    public function build($item)
    {
        $this->item = $item;
        $type       = $this->item['field_type'];
        return $this->$type();

    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function editText()
    {
        $item = $this->initItem();
        if (empty($this->item['input_type'])) $item->setInputType('text');
        return $item->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function header()
    {
        return $this->initItem()->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function date()
    {
        return $this->initItem()->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function dropdown()
    {
        return $this->initItem()->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function textView()
    {
        return $this->initItem()->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function checkbox()
    {
        $item = $this->initItem();
        if ($item->getValue() !== 0 || $item->getValue() !== 1) $item->setValue(0);
        return $item->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function multipleView()
    {
        $item  = $this->initItem(false);
        $views = [];
        $data  = isset($this->data[$this->item['name']]) ? $this->data[$this->item['name']] : [];
        foreach ($item->getViews() as $view) {
            $views[] = (new FormItemBuilder())->setData($data)->build($view);
        }
        $item->setViews($views);
        return $item->toArray();
    }


}
