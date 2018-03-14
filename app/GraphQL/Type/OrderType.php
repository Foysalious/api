<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class OrderType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Order'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'customer' => ['type' => GraphQL::type('Customer')],
            'category' => ['type' => GraphQL::type('Category')],
            'partner' => ['type' => GraphQL::type('Partner')],
            'jobs' => ['type' => Type::listOf(GraphQL::type('Job'))],
            'code' => ['type' => Type::string()],
            'paid' => ['type' => Type::float()],
            'due' => ['type' => Type::float()]
        ];
    }

    protected function resolveCustomerField($root)
    {
        return $root->order->customer;
    }

    protected function resolveCategoryField($root)
    {
        return count($root->jobs) > 0 ? $root->jobs[0]->category : null;
    }

    protected function resolveCodeField($root)
    {
        return $root->order->code();
    }

    protected function resolvePaidField($root)
    {
        if (!isset($root['paid'])) {
            $root->calculate(true);
        }
        return (float)$root->paid;
    }

    protected function resolveDueField($root)
    {
        if (!isset($root['due'])) {
            $root->calculate(true);
        }
        return (float)$root->due;
    }
}