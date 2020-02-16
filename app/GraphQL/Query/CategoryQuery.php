<?php namespace App\GraphQL\Query;

use App\Models\Category;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CategoryQuery extends Query
{
    use LocationFilter;

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
            'location' =>['name' => 'location', 'type' => Type::int()],
            'lat' =>['name' => 'lat', 'type' => Type::float()],
            'lng' =>['name' => 'lng', 'type' => Type::float()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        return Category::published()->where(function ($query) use ($args) {
            if (isset($args['slug'])) {
                $query->where('slug', $args['slug']);
            } elseif (isset($args['id'])) {
                $query->where('id', $args['id']);
            }

            $location = $this->getLocationId($args);
            if ($location) $this->filterLocation($query, $location);
        })->first();
    }
}
