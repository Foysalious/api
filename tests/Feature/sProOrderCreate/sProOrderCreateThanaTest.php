<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\District;
use App\Models\Division;
use App\Models\Thana;
use Tests\Feature\FeatureTestCase;

class sProOrderCreateThanaTest extends FeatureTestCase
{

    private $division;
    private $district;
    private $thana;

    public function setUp()
    {

        parent::setUp();

        $this->truncateTable(Division::class);

        $this->truncateTable(District::class);

        $this->truncateTable(Thana::class);

        $this->logIn();

        $this->division = factory(Division::class)->create();

        $this->district = factory(District::class)->create();

        $this->thana = factory(Thana::class)->create();

    }

    public function testSProThanaAPIForSingleThana()
    {
        //arrange

        //act
        $response = $this->get('/v3/thanas');

        $data = $response->decodeResponseJson();


        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["thanas"][0]["id"]);
        $this->assertEquals(1, $data["thanas"][0]["district_id"]);
        $this->assertEquals(4, $data["thanas"][0]["location_id"]);
        $this->assertEquals('Gulshan', $data["thanas"][0]["name"]);
        $this->assertEquals('গুলশান', $data["thanas"][0]["bn_name"]);
        $this->assertEquals(23.792496, $data["thanas"][0]["lat"]);
        $this->assertEquals(90.407806, $data["thanas"][0]["lng"]);
        $this->assertEquals(1, $data["thanas"][0]["district"]["id"]);
        $this->assertEquals('Dhaka', $data["thanas"][0]["district"]["name"]);
        $this->assertEquals('ঢাকা', $data["thanas"][0]["district"]["bn_name"]);

    }

    public function testSProThanaAPIForMultipleThana()
    {
        //arrange
        $this->thana = factory(Thana::class)->create([
            'district_id' => 1,
            'location_id' => 5,
            'name' => 'Mirpur',
            'bn_name' => 'মিরপুর',
            'lat' => 23.8223490,
            'lng' => 90.3654200
        ]);

        //act
        $response = $this->get('/v3/thanas');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["thanas"][0]["id"]);
        $this->assertEquals(1, $data["thanas"][0]["district_id"]);
        $this->assertEquals(4, $data["thanas"][0]["location_id"]);
        $this->assertEquals('Gulshan', $data["thanas"][0]["name"]);
        $this->assertEquals('গুলশান', $data["thanas"][0]["bn_name"]);
        $this->assertEquals(23.792496, $data["thanas"][0]["lat"]);
        $this->assertEquals(90.407806, $data["thanas"][0]["lng"]);
        $this->assertEquals(1, $data["thanas"][0]["district"]["id"]);
        $this->assertEquals('Dhaka', $data["thanas"][0]["district"]["name"]);
        $this->assertEquals('ঢাকা', $data["thanas"][0]["district"]["bn_name"]);
        $this->assertEquals(2, $data["thanas"][1]["id"]);
        $this->assertEquals(1, $data["thanas"][1]["district_id"]);
        $this->assertEquals(5, $data["thanas"][1]["location_id"]);
        $this->assertEquals('Mirpur', $data["thanas"][1]["name"]);
        $this->assertEquals('মিরপুর', $data["thanas"][1]["bn_name"]);
        $this->assertEquals(23.822349, $data["thanas"][1]["lat"]);
        $this->assertEquals(90.36542, $data["thanas"][1]["lng"]);
        $this->assertEquals(1, $data["thanas"][1]["district"]["id"]);
        $this->assertEquals('Dhaka', $data["thanas"][1]["district"]["name"]);
        $this->assertEquals('ঢাকা', $data["thanas"][1]["district"]["bn_name"]);

    }

    public function testSProThanaAPIForMultipleDistrict()
    {
        //arrange
        $this->district = factory(District::class)->create([
            'division_id' => 1,
            'name' => 'Faridpur',
            'bn_name' => 'ফরিদপুর',
            'lat' => 23.6070822,
            'lng' => 89.8429406
        ]);

        $this->thana = factory(Thana::class)->create([
            'district_id' => 2,
            'location_id' => 2,
            'name' => 'Faridpur Sadar',
            'bn_name' => 'ফরিদপুর সদর',
            'lat' => 23.6203524,
            'lng' => 89.8130356
        ]);

        //act
        $response = $this->get('/v3/thanas');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["thanas"][0]["id"]);
        $this->assertEquals(1, $data["thanas"][0]["district_id"]);
        $this->assertEquals(4, $data["thanas"][0]["location_id"]);
        $this->assertEquals('Gulshan', $data["thanas"][0]["name"]);
        $this->assertEquals('গুলশান', $data["thanas"][0]["bn_name"]);
        $this->assertEquals(23.792496, $data["thanas"][0]["lat"]);
        $this->assertEquals(90.407806, $data["thanas"][0]["lng"]);
        $this->assertEquals(1, $data["thanas"][0]["district"]["id"]);
        $this->assertEquals('Dhaka', $data["thanas"][0]["district"]["name"]);
        $this->assertEquals('ঢাকা', $data["thanas"][0]["district"]["bn_name"]);
        $this->assertEquals(2, $data["thanas"][1]["id"]);
        $this->assertEquals(2, $data["thanas"][1]["district_id"]);
        $this->assertEquals(2, $data["thanas"][1]["location_id"]);
        $this->assertEquals('Faridpur Sadar', $data["thanas"][1]["name"]);
        $this->assertEquals('ফরিদপুর সদর', $data["thanas"][1]["bn_name"]);
        $this->assertEquals(23.6203524, $data["thanas"][1]["lat"]);
        $this->assertEquals(89.8130356, $data["thanas"][1]["lng"]);
        $this->assertEquals(2, $data["thanas"][1]["district"]["id"]);
        $this->assertEquals('Faridpur', $data["thanas"][1]["district"]["name"]);
        $this->assertEquals('ফরিদপুর', $data["thanas"][1]["district"]["bn_name"]);

    }

    public function testSProThanaAPIForMultipleDivision()
    {
        //arrange
        $this->division = factory(Division::class)->create([
            'name' => 'Chattogram',
            'bn_name' => 'চট্টগ্রাম'
        ]);

        $this->district = factory(District::class)->create([
            'division_id' => 2,
            'name' => 'Faridpur',
            'bn_name' => 'ফরিদপুর',
            'lat' => 23.6070822,
            'lng' => 89.8429406
        ]);

        $this->thana = factory(Thana::class)->create([
            'district_id' => 2,
            'location_id' => 2,
            'name' => 'Faridpur Sadar',
            'bn_name' => 'ফরিদপুর সদর',
            'lat' => 23.6203524,
            'lng' => 89.8130356
        ]);

        //act
        $response = $this->get('/v3/thanas');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["thanas"][0]["id"]);
        $this->assertEquals(1, $data["thanas"][0]["district_id"]);
        $this->assertEquals(4, $data["thanas"][0]["location_id"]);
        $this->assertEquals('Gulshan', $data["thanas"][0]["name"]);
        $this->assertEquals('গুলশান', $data["thanas"][0]["bn_name"]);
        $this->assertEquals(23.792496, $data["thanas"][0]["lat"]);
        $this->assertEquals(90.407806, $data["thanas"][0]["lng"]);
        $this->assertEquals(1, $data["thanas"][0]["district"]["id"]);
        $this->assertEquals('Dhaka', $data["thanas"][0]["district"]["name"]);
        $this->assertEquals('ঢাকা', $data["thanas"][0]["district"]["bn_name"]);
        $this->assertEquals(2, $data["thanas"][1]["id"]);
        $this->assertEquals(2, $data["thanas"][1]["district_id"]);
        $this->assertEquals(2, $data["thanas"][1]["location_id"]);
        $this->assertEquals('Faridpur Sadar', $data["thanas"][1]["name"]);
        $this->assertEquals('ফরিদপুর সদর', $data["thanas"][1]["bn_name"]);
        $this->assertEquals(23.6203524, $data["thanas"][1]["lat"]);
        $this->assertEquals(89.8130356, $data["thanas"][1]["lng"]);
        $this->assertEquals(2, $data["thanas"][1]["district"]["id"]);
        $this->assertEquals('Faridpur', $data["thanas"][1]["district"]["name"]);
        $this->assertEquals('ফরিদপুর', $data["thanas"][1]["district"]["bn_name"]);

    }

    public function testSProThanaAPIForInvalidURL()
    {
        //arrange

        //act
        $response = $this->get('/v3/thana');

        $data = $response->decodeResponseJson();


        //assert
        $this->assertEquals('404 Not Found', $data["message"]);
    }

}
