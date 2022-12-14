<?php namespace App\GraphQL\Query;

use App\Models\Customer;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Dal\Accessor\Model as Accessor;

class ComplainQuery extends Query
{
    protected $attributes = [
        'name' => 'complain'
    ];

    public function type()
    {
        return GraphQL::type('Complain');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'job_id' => ['name' => 'job_id', 'type' => Type::int()],
            'customer_id' => ['name' => 'customer_id', 'type' => Type::int()],
            'token' => ['name' => 'token', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        if (!isset($args['id']) || !isset($args['job_id']) || !isset($args['customer_id']) || !isset($args['token'])) {
            return null;
        }

        $accessor = Accessor::where('model_name', Customer::class)->first();

        return Complain::where([
            ['id', $args['id']],
            ['customer_id', $args['customer_id']],
            ['accessor_id', $accessor->id],
        ])->first();
    }

}
