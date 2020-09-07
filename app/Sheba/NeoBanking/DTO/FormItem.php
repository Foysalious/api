<?php


namespace Sheba\NeoBanking\DTO;


use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionException;

abstract class FormItem implements Arrayable
{
    protected $field_type = 'common';
    protected $title      = 'Some Header';

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
    }

}
