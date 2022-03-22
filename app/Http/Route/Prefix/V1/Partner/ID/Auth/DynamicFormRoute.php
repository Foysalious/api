<?php

namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class DynamicFormRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['accessToken']], function ($api) {
            $api->get('/type', 'DynamicForm\\FormController@selectTypes');

            $api->group(['prefix' => 'dynamic-form'], function ($api) {
                $api->get('/{form_id}', 'DynamicForm\\FormController@getSections');
                $api->get('/{form_id}/section/{section}', 'DynamicForm\\FormController@getSectionWiseFields');
                $api->post('/{form_id}/section/{section}', 'DynamicForm\\FormController@postSectionWiseFields');
            });
        });
    }
}
