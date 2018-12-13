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
            'lat' => ['type' => Type::string()],
            'lng' => ['type' => Type::string()],
        ];
    }

    protected function resolveLatField($root, $args)
    {
        return $root->geo_informations ? (json_decode($root->geo_informations))->lat : null;
    }

    protected function resolveLngField($root, $args)
    {
        return $root->geo_informations ? (json_decode($root->geo_informations))->lng : null;
    }
}