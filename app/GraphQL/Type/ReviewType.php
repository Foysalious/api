<?php


namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ReviewType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Review'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'rating' => ['type' => Type::int()],
            'review' => ['type' => Type::string()],
            'customer' => ['type' => GraphQL::type('Customer')],
            'partner' => ['type' => GraphQL::type('Partner')],
            'category' => ['type' => GraphQL::type('Category')],
            'job' => ['type' => GraphQL::type('Job')],
            'compliments' => ['type' => Type::listOf(GraphQL::type('Compliment'))]
        ];
    }

    protected function resolveReviewField($root, $args)
    {
        if ($root->rates != null) {
            foreach ($root->rates as $rate) {
                if ($rate->rate_answer_text) {
                    return $rate->rate_answer_text;
                }
            }
        }
        return $root->review;
    }

    protected function resolveComplimentsField($root, $args)
    {
        $final = [];
        if ($root->rates != null) {
            $root->load(['rates' => function ($q) {
                $q->with('answer');
            }]);
            foreach ($root->rates as $rate) {
                if ($rate->rate_answer_id) {
                    $answer = $rate->answer;
                    array_add($answer, 'rate_question_id', $rate->rate_question_id);
                    array_add($answer, 'rate_answer_id', $rate->rate_answer_id);
                    array_push($final, $rate->answer);
                }
            }
            return $final;
        }
        return null;
    }
}