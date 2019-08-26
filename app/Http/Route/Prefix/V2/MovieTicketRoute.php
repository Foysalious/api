<?php


namespace App\Http\Route\Prefix\V2;


class MovieTicketRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'movies'], function ($api) {
            $api->get('movie-list', 'MovieTicketController@getAvailableTickets');
            $api->get('theatre-list', 'MovieTicketController@getAvailableTheatres');
            $api->get('theatre-seat-status', 'MovieTicketController@getTheatreSeatStatus');
            $api->get('history', 'MovieTicketController@history');
            $api->get('history/{history_id}', 'MovieTicketController@historyDetails');
            $api->get('promotions', 'MovieTicketController@getPromotions');
            $api->post('promotions/add', 'MovieTicketController@applyPromo');
            $api->post('book-tickets', 'MovieTicketController@bookTickets');
            $api->post('update-status', 'MovieTicketController@updateTicketStatus');
        });
    }
}
