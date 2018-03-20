<?php


namespace App\GraphQL\Query;

use App\Models\CategoryGroup;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CategoryGroupsQuery extends Query
{
    protected $attributes = [
        'name' => 'categoryGroups'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('CategoryGroup'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::listOf(Type::int())],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $category_group = CategoryGroup::query();
        $where = function ($query) use ($args) {
            if (isset($args['id'])) {
                $query->whereIn('id', $args['id']);
            }
        };
        $category_group = $category_group->where($where)->get();
        return $category_group ? $category_group : null;
    }
}