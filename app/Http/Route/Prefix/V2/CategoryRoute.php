<?php namespace App\Http\Route\Prefix\V2;

class CategoryRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'categories'], function ($api) {
            $api->get('/', 'CategoryController@getAllCategories');
            $api->group(['prefix' => '{id}'], function ($api) {
                $api->get('/', 'CategoryController@show');
                $api->get('services', 'CategoryController@getServices');
                $api->get('reviews', 'CategoryController@getReviews');
                $api->get('locations/{location}/partners', 'CategoryController@getPartnersOfLocation');
            });
        });
        $api->group(['prefix' => 'services'], function ($api) {
            $api->get('{service}', 'ServiceController@show');
        });
    }
}
