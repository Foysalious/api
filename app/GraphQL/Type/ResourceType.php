<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use GraphQL;

class ResourceType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Resource',
        'description' => 'Sheba Resource'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'profile' => ['type' => GraphQL::type('Profile')]
        ];
    }
}