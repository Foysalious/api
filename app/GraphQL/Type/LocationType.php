<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class LocationType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Location',
        'description' => 'Sheba Location'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
        ];
    }
}