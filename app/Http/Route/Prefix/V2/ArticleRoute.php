<?php namespace App\Http\Route\Prefix\V2;

class ArticleRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'help'], function ($api) {
            $api->get('article-types', 'ArticleController@getArticleTypes');
            $api->get('article-types/{type}/list', 'ArticleController@getArticles');
            $api->group(['prefix' => 'articles'], function ($api) {
                $api->group(['prefix' => '{article}'], function ($api) {
                    $api->get('/', 'ArticleController@show');
                    $api->post('like-dislike', 'ArticleController@articleLikeDislike');
                });
            });
        });
    }
}
