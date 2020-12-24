<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class ArticleListTransformer extends TransformerAbstract
{
    public function transform($article)
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
        ];
    }
}