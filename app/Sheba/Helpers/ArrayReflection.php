<?php

namespace Sheba\Helpers;

use ReflectionClass;
use ReflectionException;

class ArrayReflection
{
    const FILTER_PRIVATE   = 'isPrivate';
    const FILTER_PROTECTED = 'isProtected';
    const FILTER_PUBLIC='isPublic';

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        if (is_string($data))
            $data = json_decode($data, true);
        $data = $data ?: [];
        foreach ($data as $key => $item) {
            $this->$key = $item;
        }
        return $this;
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
    public function toArray($filter_type = self::FILTER_PUBLIC)
    {
        $reflection_class = new \ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            if (!$item->$filter_type())
                continue;
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

    public function __get($name)
    {
        if (isset($this->$name)) return $this->$name;
        return null;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function noNullableArray()
    {
        $array = $this->toArray();
        $data  = [];
        foreach ($array as $key => $val) {
            if ($val != null)
                $data[$key] = $val;
        }
        return $data;
    }


}