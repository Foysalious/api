<?php


namespace Sheba\NeoBanking\Traits;


use ReflectionClass;
use ReflectionException;

trait PrivateGetterTrait
{
    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(){
        $reflection_class = new ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            if (!$item->isPrivate())
                continue;
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

}
