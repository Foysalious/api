<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ComplainType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Complain',
        'description' => 'Sheba Category'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'complain' => ['type' => Type::string()],
            'status' => ['type' => Type::string()],
        ];
    }
}