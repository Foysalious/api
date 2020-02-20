<?php namespace Sheba\Cache;


abstract class DataStoreObject
{
    /** @var array */
    protected $generatedData;

    protected function setData(array $data)
    {
        $this->generatedData = $data;
    }

    public function get()
    {
        return $this->generatedData;
    }


    abstract public function generateData();
}