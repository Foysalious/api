<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    public function transform($article)
    {
        return [
            'id' =>   $article->id,
            'article_type_id' => $article->article_type_id,
            'article_type' => $article->articleType->name,
            'title' => $article->title,
            'description' => $article->description,
            'video_link' => $article->video_link,
        ];
    }
}