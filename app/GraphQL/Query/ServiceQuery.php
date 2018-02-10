<?php


namespace App\graphQL\Query;
use App\Models\Service;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ServiceQuery extends Query
{
    protected $attributes = [
        'name' => 'services'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Service'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()]
        ];
    }

    public function resolve($root, $args)
    {
        if (isset($args['id'])) {
            return Service::where('id' , $args['id'])->get();
        }  else {
            return Service::all();
        }
    }
}