<?php namespace Sheba\RewardShop;

class OrderStatus
{
    public $pending = 'Pending';
    public $process = 'Process';
    public $served = 'Served';

    static public function get()
    {
        return [
            'Pending' => 'Pending',
            'Process' => 'Process',
            'Served'  => 'Served'
        ];
    }
}