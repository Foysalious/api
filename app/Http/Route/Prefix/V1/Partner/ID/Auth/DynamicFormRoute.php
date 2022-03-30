<?php

namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class DynamicFormRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['accessToken']], function ($api) {
            $api->get('/type', 'DynamicForm\\FormController@selectTypes');
            $api->post('/sections/document-upload', 'DynamicForm\\FormController@uploadDocument');

            $api->group(['prefix' => 'dynamic-form'], function ($api) {
                $api->get('/', 'DynamicForm\\FormController@getSections');
                $api->get('/section', 'DynamicForm\\FormController@getSectionWiseFields');
                $api->post('/section', 'DynamicForm\\FormController@postSectionWiseFields');
            });
        });
    }
}
