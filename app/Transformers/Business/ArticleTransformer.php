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
            'video' => is_null($article->video_link) ? null : $this->getThumbnail($article)
        ];
    }

    private function getThumbnail($article)
    {
       $data = [];
       $data['link'] = $article->video_link;
       $data['thumbnail'] = $article->thumb;
       return $data;
    }
}