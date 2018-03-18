<?php

namespace App\GraphQL\Type;

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
class OrderServiceType extends GraphQlType
{
    protected $attributes = [
        'name' => 'OrderService'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()]
        ];
    }
}