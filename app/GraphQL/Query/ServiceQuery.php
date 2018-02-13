<?php


namespace App\graphQL\Query;

use App\Models\Service;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
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
            'id' => ['name' => 'id', 'type' => Type::listOf(Type::int())]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $fields = $info->getFieldSelection(10);
        if (isset($args['id'])) {
            $services = Service::query();
            foreach ($fields as $field => $keys) {
                if ($field === 'category') {
                    $services->with('category');
                }
            }
            return $services->whereIn('id', $args['id'])->get();
        } else {
            return null;
        }
    }
}