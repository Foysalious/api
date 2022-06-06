<?php

namespace App\Sheba\DynamicForm;

class CollectionFormatter
{

    private $data;

    public function setData($data): CollectionFormatter
    {
        $this->data = $data;
        return $this;
    }

    public function formatCollection()
    {
        for ($i = 0; $i < count($this->data); $i++) {
            $this->data[$i]['key'] = $this->data[$i]['id'];
            unset($this->data[$i]['id']);
            $this->data[$i]['value'] = $this->data[$i]['bn_name'];
            unset($this->data[$i]['bn_name']);
        }
        return $this->data;
    }
}
