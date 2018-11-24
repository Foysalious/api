<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use GraphQL;

class AffiliateType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Affiliate',
        'description' => 'Sheba bondhu'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'picture' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
            'mobile' => ['type' => Type::string()],
            'created_at_timestamp' => ['type' => Type::string()],
            'user_hash' => ['type' => Type::string()],
            'credit' => ['type' => Type::float()],
            'profile' => ['type' => GraphQL::type('Profile')],
        ];
    }

    protected function resolveNameField($root, $args)
    {
        return $root->profile->name;
    }

    protected function resolvePictureField($root, $args)
    {
        return $root->profile->pro_pic;
    }

    protected function resolveEmailField($root, $args)
    {
        return $root->profile->email;
    }

    protected function resolveMobileField($root, $args)
    {
        return $root->profile->mobile;
    }

    protected function resolveGenderField($root, $args)
    {
        return $root->profile->gender;
    }

    protected function resolveBirthdayField($root, $args)
    {
        return $root->profile->dob;
    }

    protected function resolveUserHashField($root, $args)
    {
        return hash_hmac('sha256', $root->id, env('INTERCOM_SECRET_KEY'));
    }

    protected function resolveCreatedAtTimestampField($root, $args)
    {
        return $root->created_at->timestamp;
    }

    protected function resolveCreditField($root, $args)
    {
        return (double)round($root->wallet, 2);
    }

}