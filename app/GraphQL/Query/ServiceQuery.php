<?php namespace App\GraphQL\Query;

use App\Models\Service;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ServiceQuery extends Query
{
    protected $attributes = [
        'name' => 'service'
    ];

    public function type()
    {
        return GraphQL::type('Service');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $fields = $info->getFieldSelection(10);
        if (!isset($args['id'])) return null;

        $service = Service::publishedForAll()->where('id', $args['id']);
        if (in_array('category', array_keys($fields))) $service->with('category');
        return $service->first();
    }
}
