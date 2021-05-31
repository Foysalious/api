<?php namespace Sheba\PartnerOrder\ConcurrentUpdateRestriction;

interface CURDataInterface
{
    public function set($value);

    public function get();

    public function getCUObject($value);

    public function check($value);

    public function remove($value);

    public function checkArray($values);

    public function getExistedKeys();

    public function setArray($values);

    public function removeArray($values);
}
