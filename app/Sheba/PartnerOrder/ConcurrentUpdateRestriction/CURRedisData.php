<?php namespace Sheba\PartnerOrder\ConcurrentUpdateRestriction;

use Sheba\Helpers\RedisHelper;

class CURRedisData implements CURDataInterface
{
    private $redisKey = 'order_closed_operation_running';
    private $existedKeys = [];

    public function set($value)
    {
        if (!$this->check($value)) RedisHelper::pushToKey($this->redisKey, $value);
    }

    public function get()
    {
        return RedisHelper::getAllByKey($this->redisKey);
    }

    public function check($value)
    {
        return in_array($value, $this->get());
    }

    public function remove($value)
    {
        RedisHelper::removeAllOccurrenceOfValueFromKey($this->redisKey, $value);
    }

    public function checkArray($values)
    {
        foreach ($values as $value) {
            if ($this->check($value)) $this->existedKeys[] = $value;
        }
        return !!$this->existedKeys;
    }

    public function getExistedKeys()
    {
        return $this->existedKeys;
    }

    public function setArray($values)
    {
        foreach ($values as $value) {
            $this->set($value);
        }
    }

    public function removeArray($values)
    {
        foreach ($values as $value) {
            $this->remove($value);
        }
    }
}