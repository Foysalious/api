<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CustomerFavoriteType extends GraphQlType
{
    protected $attributes = [
        'name' => 'CustomerFavorite'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()]
        ];
    }
}