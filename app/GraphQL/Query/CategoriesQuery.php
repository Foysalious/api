<?php

namespace App\GraphQL\Query;

use App\Models\Category;
use App\Models\HyperLocal;
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
            'isMaster' => ['name' => 'isMaster', 'type' => Type::boolean()],
            'location' =>['name' => 'location', 'type' => Type::int()],
            'lat' =>['name' => 'lat', 'type' => Type::float()],
            'lng' =>['name' => 'lng', 'type' => Type::float()],
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
            if(isset($args['location'])) {
                $location = $args['location'];

                $query->whereHas('locations' , function($q) use ($location) {
                    $q->where('locations.id', $location);
                });
            } else if(isset($args['lat']) && isset($args['lng']))  {
                $lat = $args['lat'];
                $lng = $args['lng'];
                $query->whereHas('locations' , function($q) use ($lat, $lng) {
                    $hyperLocation= HyperLocal::insidePolygon((double) $lat, (double) $lng)->with('location')->first();
                    if(!is_null($hyperLocation)) {
                        $location = $hyperLocation->location;
                        $q->where('locations.id', $location->id);
                    }
                });
            }
            $query->published();
        };
        $fields = $info->getFieldSelection(1);
        if (in_array('children', $fields)) {
            if(isset($args['location'])) {
                $location = $args['location'];
                $category = $category->with('children', function($query) use ($location) {
                    $query->whereHas('locations' , function($q) use ($location) {
                        $q->where('locations.id', $location);
                    });
                });;
            }  else if(isset($args['lat']) && isset($args['lng'])) {
                $lat = $args['lat'];
                $lng = $args['lng'];
                $category = $category->with(['children' => function($query) use ($lat, $lng) {
                    $query->whereHas('locations' , function($q) use ($lat, $lng) {
                        $hyperLocation= HyperLocal::insidePolygon((double) $lat, (double) $lng)->with('location')->first();
                        if(!is_null($hyperLocation)) {
                            $location = $hyperLocation->location;
                            $q->where('locations.id', $location->id);
                        }
                    });
                }]);
            }
        }
        $categories = $category->where($where)->get();
        return $categories ? $categories : null;
    }
}