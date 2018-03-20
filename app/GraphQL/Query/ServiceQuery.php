<?php

namespace App\GraphQL\Query;

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
        if (isset($args['id'])) {
            $service = Service::query();
            foreach ($fields as $field => $keys) {
                if ($field === 'category') {
                    $service->with('category');
                }
            }
            return $service->published()->where('id', $args['id'])->first();
        } else {
            return null;
        }
    }
}