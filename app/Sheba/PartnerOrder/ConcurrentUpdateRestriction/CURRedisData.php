<?php namespace Sheba\PartnerOrder\ConcurrentUpdateRestriction;

use Carbon\Carbon;
use Sheba\Helpers\RedisHelper;

class CURRedisData implements CURDataInterface
{
    private $redisKey = 'order_closed_operation_running';
    private $existedKeys = [];
    private $partnerOrderValue = null;

    public function set($value)
    {
        if (!$this->check($value)) RedisHelper::pushToKey($this->redisKey, json_encode([
            'partner_order_id' => $value,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]));
    }

    public function get()
    {
        return RedisHelper::getAllByKey($this->redisKey);
    }

    public function getCUObject($value)
    {
        return $this->check($value);
    }

    public function check($value)
    {
        foreach ($this->get() as $row) {
            if (json_decode($row)->partner_order_id == $value) {
                $this->partnerOrderValue = [
                    'partner_order_id' => json_decode($row)->partner_order_id,
                    'created_at' => json_decode($row)->created_at
                ];
                break;
            }
        }
        return ($this->partnerOrderValue);
    }

    public function remove($value)
    {
        RedisHelper::removeAllOccurrenceOfValueFromKey($this->redisKey, json_encode($this->getCUObject($value)));
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
