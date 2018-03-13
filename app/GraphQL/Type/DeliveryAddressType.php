<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class DeliveryAddressType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Address',
        'description' => 'Customer Delivery Addresses'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'address' => ['type' => Type::string()],
            'latitude' => ['type' => Type::string()],
            'longitude' => ['type' => Type::string()],
        ];
    }
}