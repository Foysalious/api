<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use Redis;

class PartnerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Partner',
        'description' => 'Sheba Partner'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'logo' => ['type' => Type::string()],
            'slug' => ['type' => Type::string()],
            'locations' => ['type' => Type::listOf(GraphQL::type('Location'))]
        ];
    }

    protected function resolveSlugField($root, $args)
    {
        return $root->sub_domain;
    }
}