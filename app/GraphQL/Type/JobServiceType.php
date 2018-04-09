<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class JobServiceType extends GraphQlType
{
    protected $attributes = [
        'name' => 'JobService'
    ];

    public function fields()
    {
        return [
            'name' => ['type' => Type::string()],
            'id' => ['type' => Type::int()],
            'options' => ['type' => Type::string()],
            'unit' => ['type' => Type::string()],
            'unit_price' => ['type' => Type::float()],
            'quantity' => ['type' => Type::float()]
        ];
    }
}