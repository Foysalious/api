<?php
/**
 * khairun
 * Wed,14 July
 */


namespace Factory;


use Sheba\Dal\SubscriptionWisePaymentGateway\Model;

class SubscriptionWisePaymentGatewaysFactory extends Factory
{
    private $topupCharges = [
        [
            'key' => 'mock',
            'name' => 'Mock',
            'commission' => 1,
            'otf_commission' => 1,
        ]
    ];
    private $gatewayCharge = [
        [
            'key'=> 'nagad',
            'name'=> 'Nagad',
            'fixed_charge'=> 3,
            'gateway_charge'=> 2,
            ],
        [
            'key'=> 'bkash',
            'name'=> 'bKash',
            'fixed_charge'=> 3,
            'gateway_charge'=> 2
        ]
    ];
    protected function getModelClass()
    {
        // TODO: Implement getModelClass() method.
        return Model::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds,[
            'package_id'=>1,
            //'gateway_charges'=>json_decode($this->gatewayCharge),
            //'topup_charges'=>json_encode($this->topupCharges),
            'expired'=>1,
            'updated_by'=>1
  ]);
        // TODO: Implement getData() method.
    }
}