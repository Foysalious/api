<?php namespace Sheba\Cache;


interface DataStoreObject
{
    public function get(): array;

    public function generateData();
}