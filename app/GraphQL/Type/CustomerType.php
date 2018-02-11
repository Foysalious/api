<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CustomerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Customer',
        'description' => 'Sheba customer'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::int()
            ],
            'profile' => [
                'type' => GraphQL::type('Profile'),
            ],
        ];
    }
}