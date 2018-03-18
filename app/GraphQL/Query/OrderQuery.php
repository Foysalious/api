<?php

namespace App\GraphQL\Query;

use App\Models\Customer;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class OrderQuery extends Query
{
    protected $attributes = [
        'name' => 'order'
    ];

    public function type()
    {
        return GraphQL::type('Order');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'customer_id' => ['name' => 'customer_id', 'type' => Type::int()],
            'token' => ['name' => 'token', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        if (!isset($args['id']) || !isset($args['token']) || !isset($args['customer_id'])) {
            return null;
        }
        $customer = Customer::where([
            ['id', $args['customer_id']],
            ['remember_token', $args['token']],
        ])->with(['partnerOrders' => function ($q) use ($args) {
            return $q->where('partner_orders.id', $args['id']);
        }])->first();
        if ($customer != null) {
            return $customer->partnerOrders ? $customer->partnerOrders->first() : null;
        }
    }
}