<?php namespace Tests\Feature\Order\OrderPlace;

use App\Models\Profile;
use GuzzleHttp\Client;
use Tests\Feature\FeatureTestCase;

class RentACarOrderPlaceTest extends FeatureTestCase
{
    /*public function testHalfDay()
    {
        $client = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $response = $client->get('/v2/settings/car');
        $this->assertEquals(200, $response->getStatusCode());
        $settings = json_decode($response->getBody(), true)['settings'];

        $times = $client->get('/v2/times');
        $this->assertEquals(200, $times->getStatusCode());
        $valid_times = json_decode($times->getBody(), true)['valid_times'];

        $locations = collect($settings['locations']);
        $pick_up_locations = $locations->filter(function ($location) {
            return (int)$location['is_available_for_pickup'] == 1;
        });
        $pick_up_location = $pick_up_locations->random(1);
        $half_day_service = $settings['sub_cats']['inside_city']['services']['half_day'];
        $answers = $half_day_service['questions'][0]['answers'];
        $services = json_encode([
            array(
                'id' => $half_day_service['id'],
                'quantity' => $half_day_service['min_quantity'],
                'pick_up_location_id' => $pick_up_location['id'],
                'pick_up_location_type' => $pick_up_location['type'],
                'pick_up_location_address' => str_random(20),
                'pick_up_area' => $pick_up_location['name'],
                'option' => [array_rand($answers)]
            )
        ]);
        $partner = $this->getPartner($client, $services, $valid_times);
        $this->orderPlace($services, $partner, $valid_times);
    }

    public function testFullDay()
    {
        $client = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $response = $client->get('/v2/settings/car');
        $this->assertEquals(200, $response->getStatusCode());
        $settings = json_decode($response->getBody(), true)['settings'];
        $locations = collect($settings['locations']);

        $times = $client->get('/v2/times');
        $this->assertEquals(200, $times->getStatusCode());
        $valid_times = json_decode($times->getBody(), true)['valid_times'];
        $pick_up_locations = $locations->filter(function ($location) {
            return (int)$location['is_available_for_pickup'] == 1;
        });
        $pick_up_location = $pick_up_locations->random(1);
        $full_day_service = $settings['sub_cats']['inside_city']['services']['full_day'];
        $answers = $full_day_service['questions'][0]['answers'];
        $services = json_encode([
            array(
                'id' => $full_day_service['id'],
                'quantity' => $full_day_service['min_quantity'],
                'pick_up_location_id' => $pick_up_location['id'],
                'pick_up_location_type' => $pick_up_location['type'],
                'pick_up_location_address' => str_random(20),
                'pick_up_area' => $pick_up_location['name'],
                'option' => [array_rand($answers)]
            )
        ]);
        $partner = $this->getPartner($client, $services, $valid_times);
        $this->orderPlace($services, $partner, $valid_times);

    }

    public function testOneWay()
    {
        $client = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $response = $client->get('/v2/settings/car');
        $this->assertEquals(200, $response->getStatusCode());
        $settings = json_decode($response->getBody(), true)['settings'];
        $locations = collect($settings['locations']);

        $times = $client->get('/v2/times');
        $this->assertEquals(200, $times->getStatusCode());
        $valid_times = json_decode($times->getBody(), true)['valid_times'];
        $pick_up_locations = $locations->filter(function ($location) {
            return (int)$location['is_available_for_pickup'] == 1;
        });
        $pick_up_location = $pick_up_locations->random(1);
        $destination_locations = $locations->filter(function ($location) use ($pick_up_location) {
            return $pick_up_location['district_id'] != $location['district_id'];
        });
        $destination_location = $destination_locations->random(1);
        $one_way_service = $settings['sub_cats']['outside_city']['services']['one_way'];
        $answers = $one_way_service['questions'][0]['answers'];
        $services = json_encode([
            array(
                'id' => $one_way_service['id'],
                'quantity' => $one_way_service['min_quantity'],
                'pick_up_location_id' => $pick_up_location['id'],
                'pick_up_location_type' => $pick_up_location['type'],
                'pick_up_location_address' => str_random(20),
                'pick_up_area' => $pick_up_location['name'],
                'destination_location_id' => $destination_location['id'],
                'destination_location_type' => $destination_location['type'],
                'destination_location_address' => str_random(20),
                'destination_area' => $destination_location['name'],
                'option' => [array_rand($answers)]
            )
        ]);
        $partner = $this->getPartner($client, $services, $valid_times);
        $this->orderPlace($services, $partner, $valid_times);
    }

    public function testRoundTrip()
    {
        $client = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $response = $client->get('/v2/settings/car');
        $this->assertEquals(200, $response->getStatusCode());
        $settings = json_decode($response->getBody(), true)['settings'];
        $locations = collect($settings['locations']);

        $times = $client->get('/v2/times');
        $this->assertEquals(200, $times->getStatusCode());
        $valid_times = json_decode($times->getBody(), true)['valid_times'];
        $pick_up_locations = $locations->filter(function ($location) {
            return (int)$location['is_available_for_pickup'] == 1;
        });
        $pick_up_location = $pick_up_locations->random(1);
        $destination_locations = $locations->filter(function ($location) use ($pick_up_location) {
            return $pick_up_location['district_id'] != $location['district_id'];
        });
        $destination_location = $destination_locations->random(1);

        $round_trip_service = $settings['sub_cats']['outside_city']['services']['round_trip'];
        $answers = $round_trip_service['questions'][0]['answers'];
        $services = json_encode([
            array(
                'id' => $round_trip_service['id'],
                'quantity' => $round_trip_service['min_quantity'],
                'pick_up_location_id' => $pick_up_location['id'],
                'pick_up_location_type' => $pick_up_location['type'],
                'pick_up_location_address' => str_random(20),
                'pick_up_area' => $pick_up_location['name'],
                'destination_location_id' => $destination_location['id'],
                'destination_location_type' => $destination_location['type'],
                'destination_location_address' => str_random(20),
                'destination_area' => $destination_location['name'],
                'option' => [array_rand($answers)]
            )
        ]);
        $partner = $this->getPartner($client, $services, $valid_times);
        $this->orderPlace($services, $partner, $valid_times);
    }

    public function testBodyRent()
    {
        $client = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $response = $client->get('/v2/settings/car');
        $this->assertEquals(200, $response->getStatusCode());
        $settings = json_decode($response->getBody(), true)['settings'];
        $locations = collect($settings['locations']);

        $times = $client->get('/v2/times');
        $this->assertEquals(200, $times->getStatusCode());
        $valid_times = json_decode($times->getBody(), true)['valid_times'];
        $pick_up_locations = $locations->filter(function ($location) {
            return (int)$location['is_available_for_pickup'] == 1;
        });
        $pick_up_location = $pick_up_locations->random(1);
        $destination_locations = $locations->filter(function ($location) use ($pick_up_location) {
            return $pick_up_location['district_id'] != $location['district_id'];
        });
        $destination_location = $destination_locations->random(1);

        $body_rent_service = $settings['sub_cats']['outside_city']['services']['body_rent'];
        $answers = $body_rent_service['questions'][0]['answers'];
        $services = json_encode([
            array(
                'id' => $body_rent_service['id'],
                'quantity' => $body_rent_service['min_quantity'],
                'pick_up_location_id' => $pick_up_location['id'],
                'pick_up_location_type' => $pick_up_location['type'],
                'pick_up_location_address' => str_random(20),
                'pick_up_area' => $pick_up_location['name'],
                'destination_location_id' => $destination_location['id'],
                'destination_location_type' => $destination_location['type'],
                'destination_location_address' => str_random(20),
                'destination_area' => $destination_location['name'],
                'option' => [array_rand($answers)]
            )
        ]);
        $partner = $this->getPartner($client, $services, $valid_times);
        $this->orderPlace($services, $partner, $valid_times);
    }*/

    private function getPartner($client, $services, $valid_times)
    {
        $partner_list = $client->get('/v2/locations/11/partners', [
            'query' =>
                [
                    'services' => $services,
                    'date' => date('Y-m-d'),
                    'time' => key($valid_times)

                ]
        ]);
        $data = json_decode($partner_list->getBody(), true);
        $this->assertEquals(200, $data['code']);
        $this->assertArrayHasKey('partners', $data);
        return $data['partners'][0];
    }

    private function orderPlace($services, $partner, $valid_times)
    {
        $profile = Profile::find(11);
        $this->json('POST', '/v2/customers/11/orders', [
            'services' => $services,
            'date' => date('Y-m-d'),
            'time' => key($valid_times),
            'partner' => $partner['id'],
            'name' => $profile->name,
            'mobile' => $profile->mobile,
            'remember_token' => $profile->customer->remember_token,
            'sales_channel' => 'Web',
            'payment_method' => 'cod',
            'additional_information' => str_random(50),
            'location' => 11,
            'address_id' => $profile->customer->delivery_addresses->first()->id
        ])->seeJsonStructure(['message', 'code', 'link', 'job_id', 'order_code']);
    }
}
