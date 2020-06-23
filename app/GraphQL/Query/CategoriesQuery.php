<?php namespace App\GraphQL\Query;

use App\Models\Category;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CategoriesQuery extends Query
{
    use LocationFilter;

    protected $attributes = [
        'name' => 'categories'
    ];

    public function type()
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 120);

        return Type::listOf(GraphQL::type('Category'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::listOf(Type::int())],
            'isMaster' => ['name' => 'isMaster', 'type' => Type::boolean()],
            'location' =>['name' => 'location', 'type' => Type::int()],
            'lat' =>['name' => 'lat', 'type' => Type::float()],
            'lng' =>['name' => 'lng', 'type' => Type::float()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $category = Category::published();

        $location = $this->getLocationId($args);

        $where = function ($query) use ($args, $location) {
            if (isset($args['id'])) $query->whereIn('id', $args['id']);

            if (isset($args['isMaster'])) {
                $args['isMaster'] ? $query->parent() : $query->child();
            }

            if ($location) $this->filterLocation($query, $location);
        };

        $fields = $info->getFieldSelection(1);
        if (in_array('children', $fields) && $location) {
            $category = $category->with('children', function ($query) use ($location) {
                $this->filterLocation($query, $location);
            });
        }
        $categories = $category->where($where)->get();
        return $categories ? $categories : null;
    }
}
