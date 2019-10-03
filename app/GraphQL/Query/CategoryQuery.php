<?php

namespace App\GraphQL\Query;

use App\Models\Category;
use App\Models\HyperLocal;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use Illuminate\Support\Facades\DB;

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
            'location' =>['name' => 'location', 'type' => Type::int()],
            'lat' =>['name' => 'lat', 'type' => Type::float()],
            'lng' =>['name' => 'lng', 'type' => Type::float()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        return Category::query()->where(function ($query) use ($args) {
            if (isset($args['slug'])) {
                $query->where('slug', $args['slug']);
            } elseif (isset($args['id'])) {
                $query->where('id', $args['id']);
            }

            if(isset($args['location'])) {
                $location = $args['location'];

                $query->whereHas('locations' , function($q) use ($location) {
                    $q->where('locations.id', $location);
                });
            } else if(isset($args['lat']) && isset($args['lng']))  {
                $lat = $args['lat'];
                $lng = $args['lng'];
                $query->whereHas('locations' , function($q) use ($lat, $lng) {
                    $hyper_location = HyperLocal::insidePolygon((double) $lat, (double) $lng)->with('location')->first();
                    if(!is_null($hyper_location)) {
                        $q->where('locations.id', $hyper_location->location->id);
                    }
                });
            }

            $query->published();
        })->first();
    }

}