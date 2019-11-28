<?php

namespace Sheba\Loan\DS;
trait ReflectionArray
{
    public function __construct($data)
    {
        if (is_string($data))
            $data = json_decode($data, true);
        $data = $data ?: [];
        foreach ($data as $key => $item) {
            $this->$key = $item;
        }
    }

    /**
     * @return false|string
     * @throws \ReflectionException
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $reflection_class = new \ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }
}
