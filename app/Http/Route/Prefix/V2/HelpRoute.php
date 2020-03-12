<?php namespace App\Http\Route\Prefix\V2;

class HelpRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'help'], function ($api) {
            $api->get('article-types', 'Help\ArticleController@getArticleTypes');
            $api->get('article-types/{type}/list', 'Help\ArticleController@getArticles');
            $api->group(['prefix' => 'articles'], function ($api) {
                $api->group(['prefix' => '{article}'], function ($api) {
                    $api->get('/', 'Help\ArticleController@show');
                    $api->post('like-dislike', 'Help\ArticleController@articleLikeDislike');
                });
            });
        });
    }
}
