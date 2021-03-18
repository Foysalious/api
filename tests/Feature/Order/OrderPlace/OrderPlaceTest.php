<?php namespace Tests\Feature\Order\OrderPlace;

use App\Models\Category;
use App\Models\Location;
use App\Models\Profile;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Tests\Feature\FeatureTestCase;

class OrderPlaceTest extends FeatureTestCase
{
    protected $profile;
    protected $location;
    protected $category;
    protected $service;
    protected $validDate;
    protected $validTime;

    /** @var Collection */

    public function setUp()
    {
        parent::setUp();
        $this->profile = Profile::find(11);
        $this->location = Location::find(4);
    }

    public function testCategoriesTree()
    {
        $response = $this->get("/v3/categories/tree?location_id=".$this->location->id);
        $response->assertResponseOk();
        $data = $response->decodeResponseJson();
        $categories = collect($data['categories']);
        $this->category = $categories->first();
    }

    public function testServices()
    {
        $geo = json_decode($this->location->geo_informations);
        $response = $this->get("/v3/categories/".$this->category->id."/services?lat=".$geo->lat. "&lng=".$geo->lng."&location_id=".$this->location->id);
        $response->assertResponseOk();
        $data = $response->decodeResponseJson();
        $services = collect($data['category']['services']);
        $this->service = $services->first();
    }

    public function testValidTimes()
    {
        $response = $this->get('/v3/times?limit=14&category=14');
        $response->assertResponseOk();
        $data = $response->decodeResponseJson();
        $dates = collect($data['dates']);
        $valid_date = $dates->first();
        $this->validDate = $valid_date['value'];
        foreach($valid_date['slots'] as $slot) {
            if ($slot['is_available'] && $slot['is_valid']){
                $this->validTime = $slot['key'];
                return;
            }
        }
    }

    public function testOrderPlace()
    {
        $url = config('sheba.api_url') . "/v3/customers/".$this->profile->customer->id."/orders";
        $services = [
                'id' => $this->service->id,
                'quantity' => 10,
                'option' => [0,0]
        ];
        $this->json('POST', $url, ['form_params' => [
            'services' => $services,
            'name' => $this->profile->name,
            'mobile' => $this->profile->mobile,
            'remember_token' => $this->profile->customer->remember_token,
            'sales_channel' => 'Web',
            'payment_method' => 'cod',
            'date' => $this->validDate,
            'time' => $this->validTime,
            'additional_information' => '',
            'address_id' => $this->profile->customer->delivery_addresses()->first()->id,
            'partner' => 0
        ]])->seeJsonStructure(['message', 'code', 'link', 'job_id', 'order_code']);
    }
}
