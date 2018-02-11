<?php

namespace App\graphQL\Mutation;

use App\Models\Customer;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Mutation;

class UpdateProfileMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateProfile'
    ];

    public function type()
    {
        return GraphQL::type('Profile');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'email' => ['name' => 'email', 'type' => Type::string()],
            'name' => ['name' => 'name', 'type' => Type::string()],
            'type' => ['name' => 'type', 'type' => Type::nonNull(Type::string())],
            'remember_token' => ['name' => 'remember_token', 'type' => Type::string()],
        ];
    }

    public function rules()
    {
        return [
            'id' => ['required'],
            'type' => ['required'],
            'remember_token' => ['required'],
        ];
    }

    public function authorize($root, $args)
    {
        $class_name = "App\\Models\\" . $args['type'];
        $avatar = $class_name::with('profile')->where([
            ['id', $args['id']],
            ['remember_token', $args['remember_token']],
        ])->first();
        if ($avatar) {
            return true;
        }
    }

    public function resolve($root, $args)
    {
        $class_name = "App\\Models\\" . $args['type'];
        $avatar = $class_name::with('profile')->where([
            ['id', $args['id']],
            ['remember_token', $args['remember_token']],
        ])->first();

        $profile = $avatar->profile;
        if (isset($args['email'])) {
            $profile->email = $args['email'];
        }
        if (isset($args['name'])) {
            $profile->name = $args['name'];
        }
        $profile->update();

        return $profile;
    }
}