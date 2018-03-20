<?php

namespace App\GraphQL\Query;

use App\Models\Customer;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class CustomerQuery extends Query
{
    protected $attributes = [
        'name' => 'customer'
    ];

    public function type()
    {
        return GraphQL::type('Customer');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int(), 'description' => 'Customer Id, required'],
            'token' => ['name' => 'token', 'type' => Type::string(), 'description' => 'Customer token, required']
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        if (!isset($args['id']) || !isset($args['token'])) {
            return null;
        }
        $customer = Customer::where([['id', $args['id']], ['remember_token', $args['token']]])->first();
        return $customer ? $customer : null;
    }

}