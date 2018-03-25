<?php

namespace App\GraphQL\Query;

use App\Models\Category;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CategoryQuery extends Query
{
    protected $attributes = [
        'name' => 'category'
    ];

    public function type()
    {
        return GraphQL::type('Category');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'slug' => ['name' => 'slug', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $category = Category::query();
        $where = function ($query) use ($args) {
            if (isset($args['slug'])) {
                $query->where('slug', $args['slug']);
            } elseif (isset($args['id'])) {
                $query->where('id', $args['id']);
            }
            $query->published();
        };
        $category = $category->where($where)->first();
        return $category ? $category : null;
    }

}