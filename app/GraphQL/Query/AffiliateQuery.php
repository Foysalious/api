<?php

namespace App\GraphQL\Query;

use App\Models\Affiliate;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class AffiliateQuery extends Query
{
    protected $attributes = [
        'name' => 'affiliate'
    ];

    public function type()
    {
        return GraphQL::type('Affiliate');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int(), 'description' => 'Affiliate Id, required'],
            'token' => ['name' => 'token', 'type' => Type::string(), 'description' => 'Affiliate token, required']
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        if (!isset($args['id']) || !isset($args['token'])) {
            return null;
        }
        $affiliate = Affiliate::where([['id', $args['id']], ['remember_token', $args['token']]])->first();
        return $affiliate ? $affiliate : null;
    }
}