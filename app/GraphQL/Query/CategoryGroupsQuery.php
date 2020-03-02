<?php namespace App\GraphQL\Query;

use App\Models\CategoryGroup;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CategoryGroupsQuery extends Query
{
    use LocationFilter;

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
            'for' => ['name' => 'for', 'type' => Type::string()],
            'location' =>['name' => 'location', 'type' => Type::int()],
            'lat' =>['name' => 'lat', 'type' => Type::float()],
            'lng' =>['name' => 'lng', 'type' => Type::float()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $category_group = CategoryGroup::query();

        $where = function ($query) use ($args) {
            if (isset($args['id'])) $query->whereIn('id', $args['id']);

            if (isset($args['for'])) {
                $for = 'publishedFor' . ucwords($args['for']);
                $query->$for();
            }

            $location = $this->getLocationId($args);
            if ($location) $this->filterLocation($query, $location);
        };

        $fields = $info->getFieldSelection(1);
        if (in_array('categories', $fields)) $category_group = $category_group->with('categories');
        $category_group = $category_group->where($where)->orderBy('order', 'asc')->get();
        return $category_group ? $category_group : null;
    }
}
