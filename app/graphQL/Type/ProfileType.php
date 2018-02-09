<?php

namespace App\graphQL\Type;

use App\Models\Profile;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ProfileType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Profile',
        'model' => Profile::class
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'pro_pic' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
            'mobile' => ['type' => Type::string()],
            'gender' => ['type' => Type::string()],
            'birthday' => ['type' => Type::string()],
            'address' => ['type' => Type::string()],
        ];
    }

    protected function resolveBirthdayField($root, $args)
    {
        return $root->dob;
    }
}