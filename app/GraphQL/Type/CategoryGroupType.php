<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CategoryGroupType extends GraphQlType
{
    protected $attributes = [
        'name' => 'CategoryGroup',
        'description' => 'Sheba CategoryGroup'
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
            'app_thumb' => ['type' => Type::string()],
            'app_banner' => ['type' => Type::string()],
            'is_published_for_app' => ['type' => Type::int()],
            'is_published_for_web' => ['type' => Type::int()],
            'icon' => ['type' => Type::string()],
            'icon_png' => ['type' => Type::string()],
            'categories' => ['type' => Type::listOf(GraphQL::type('Category'))],
            'updated_at_timestamp' => ['type' => Type::int(), 'description' => 'Timestamp when any of the row information has been last updated']
        ];
    }

    public function resolveCategoriesField($root)
    {
        $root->load(['categories' => function ($q) {
            $q->orderBy('id', 'desc');
        }]);
        return $root->categories;
    }

    public function resolveUpdatedAtTimeStampField($root)
    {
        return $root->updated_at->timestamp;
    }

}