<?php

namespace App\GraphQL\Query;

use App\Models\Partner;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class PartnerQuery extends Query
{
    protected $attributes = [
        'name' => 'partners'
    ];

    public function type()
    {
        return GraphQL::type('Partner');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        if (!isset($args['id'])) {
            return null;
        }
        $partner = Partner::where('id', $args['id'])->first();
        return $partner;
    }
}