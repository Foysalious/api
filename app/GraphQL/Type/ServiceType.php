<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use GraphQL;
class ServiceType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Service',
        'description' => 'Sheba service'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'category_id' => ['type' => Type::int()],
            'name' => ['type' => Type::string(),],
            'slug' => ['type' => Type::string()],
            'description' => ['type' => Type::string()],
            'unit' => ['type' => Type::string()],
            'min_quantity' => ['type' => Type::float()],
            'publication_status' => ['type' => Type::int()],
            'thumb' => ['type' => Type::string()],
            'banner' => ['type' => Type::string()],
            'faqs' => ['type' => Type::string()],
            'variable_type' => ['type' => Type::string()],
            'variables' => ['type' => Type::string()],
            'category' => ['type' => GraphQL::type('Category')]
        ];
    }

}