<?php

namespace App\graphQL\Query;

use App\Models\Customer;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CustomerQuery extends Query
{
    protected $attributes = [
        'name' => 'customers'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Customer'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'remember_token' => ['name' => 'remember_token', 'type' => Type::string()]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $fields = $info->getFieldSelection($depth = 3);
        $customers = Customer::query();
        foreach ($fields as $field => $keys) {
            if ($field === 'profile') {
                $customers->with('profile');
            }
        }
        $where = function ($query) use ($args) {
            if (isset($args['id'])) {
                $query->where('id',$args['id']);
            }
            if (isset($args['remember_token'])) {
                $query->where('remember_token',$args['remember_token']);
            }
        };
        return $customers->where($where)->get();
    }
}