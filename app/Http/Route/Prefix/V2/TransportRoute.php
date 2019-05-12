<?php namespace App\Http\Route\Prefix\V2;

class TransportRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'transport'], function ($api) {
            $api->group(['prefix' => 'bus-ticket'], function ($api) {
                $api->get('pickup-places', 'BusTicketController@getAvailablePickupPlaces');
                $api->get('destination-places', 'BusTicketController@getAvailableDestinationPlaces');
                $api->get('available-coaches', 'BusTicketController@getAvailableCoaches');
                $api->get('seat-status', 'BusTicketController@getSeatStatus');
                $api->get('promotions', 'BusTicketController@getPromotions');
                $api->post('promotions/add', 'BusTicketController@applyPromo');
                $api->get('history', 'BusTicketController@history');
                $api->get('history/{history_id}', 'BusTicketController@historyDetails');
                $api->get('cancel/{order_id}', 'BusTicketController@cancelTicket');

                $api->post('book', 'BusTicketController@book');
                $api->post('pay', 'BusTicketController@pay');
            });
        });
    }
}
