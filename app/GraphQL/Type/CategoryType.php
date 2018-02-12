<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CategoryType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Category',
        'description' => 'Sheba Category'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'short_description' => ['type' => Type::string()],
            'long_description' => ['type' => Type::string()],
            'thumb' => ['type' => Type::string()],
            'banner' => ['type' => Type::string()],
            'publication_status' => ['type' => Type::int()],
            'icon' => ['type' => Type::int()],
            'questions' => ['type' => Type::int()],
        ];
    }
}