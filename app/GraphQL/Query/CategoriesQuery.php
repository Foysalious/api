<?php

namespace App\GraphQL\Query;

use App\Models\Category;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CategoriesQuery extends Query
{
    protected $attributes = [
        'name' => 'categories'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Category'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::listOf(Type::int())],
            'isMaster' => ['name' => 'isMaster', 'type' => Type::boolean()]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $category = Category::query();
        $where = function ($query) use ($args) {
            if (isset($args['id'])) {
                $query->whereIn('id', $args['id']);
            }
            if (isset($args['isMaster'])) {
                if ($args['isMaster']) {
                    $query->where('parent_id', null);
                } else {
                    $query->where('parent_id', '<>', null);
                }
            }
            $query->published();
        };
        $categories = $category->where($where)->get();
        return $categories ? $categories : null;
    }
}