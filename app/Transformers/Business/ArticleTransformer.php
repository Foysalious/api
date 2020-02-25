<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    public function transform($article)
    {
        return [
            'id' =>   $article->id,
            'title' => $article->title,
            'description' => $article->description,
            'video_link' => $article->video_link
        ];
    }
}