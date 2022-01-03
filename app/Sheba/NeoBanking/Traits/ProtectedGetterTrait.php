<?php namespace Sheba\NeoBanking\Traits;


use ReflectionClass;
use ReflectionException;

trait ProtectedGetterTrait
{
    /**
     * @return array
     */
    public function toArray(){
        $reflection_class = new ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            if (!$item->isProtected())
                continue;
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

}
