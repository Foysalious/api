<?php

namespace App\GraphQL\Type;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class UspType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Usp',
        'description' => 'Sheba Category'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()]
        ];
    }
}