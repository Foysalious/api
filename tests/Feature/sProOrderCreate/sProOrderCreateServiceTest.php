<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\PartnerResource;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;
use Illuminate\Support\Facades\DB;

class sProOrderCreateServiceTest extends FeatureTestCase
{

    private $dummyToken;
    private $service;

    public function setUp()
    {

        parent::setUp();

        DB::table('partner_service')->delete();

        $this->truncateTable(Service::class);

        $this->truncateTable(Category::class);

        $this->truncateTable(PartnerResource::class);

        $this->logIn();

        $master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $master_category->id,
            'publication_status' => 1
        ]);

        $this->service = factory(Service::class)->create([
            'name' => 'Good Service',
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1,
            'is_published_for_backend' => 1,
            'app_thumb' => 'https://lorempixel.com/640/480/?46089'
        ]);

        $this->partner -> update([
            'geo_informations' => '{"lat":"23.788099544655","lng":"90.412001016086","radius":"500"}'
        ]);

        DB::insert('insert into partner_service(partner_id,service_id, created_by, created_by_name, updated_by, updated_by_name) values (?, ?, ?, ?, ?, ?)', [1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam']);

    }

    public function testSProServiceAPIWithValidLatLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=23.788099544655&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["services"][0]["id"]);
        $this->assertEquals('Good Service', $data["services"][0]["name"]);
        $this->assertEquals('Fixed', $data["services"][0]["variable_type"]);
        $this->assertEquals(1, $data["services"][0]["min_quantity"]);
        $this->assertEquals(null, $data["services"][0]["unit"]);
        $this->assertEquals('https://lorempixel.com/640/480/?46089', $data["services"][0]["app_thumb"]);
        $this->assertEquals(2, $data["services"][0]["category_id"]);
        $this->assertEquals([], $data["services"][0]["option_prices"]);
        $this->assertEquals([], $data["services"][0]["questions"]);
        $this->assertEquals(1700, $data["services"][0]["fixed_price"]);
        $this->assertEquals(0, $data["services"][0]["is_rent_a_car"]);

    }

    public function testSProServiceAPIWithInvalidAuthToken()
    {
        //arrange

        $this->dummyToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImltYWdlIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9yZXNvdXJjZXMvYXZhdGFyLzE2MjI1MjA3NDNfa2F6aWZhaGR6YWt3YW4uanBnIiwicHJvZmlsZSI6eyJpZCI6MjYyNTM1LCJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImVtYWlsX3ZlcmlmaWVkIjowfSwiY3VzdG9tZXIiOnsiaWQiOjE5MDUwMX0sInJlc291cmNlIjp7ImlkIjo0NjMzMSwicGFydG5lciI6eyJpZCI6MjE2NzA0LCJuYW1lIjoiIiwic3ViX2RvbWFpbiI6InNlcnZpY2luZy1iZCIsImxvZ28iOiJodHRwczovL3MzLmFwLXNvdXRoLTEuYW1hem9uYXdzLmNvbS9jZG4tc2hlYmFkZXYvaW1hZ2VzL3BhcnRuZXJzL2xvZ29zLzE2MjI0NDM4ODBfc2VydmljaW5nYmQucG5nIiwiaXNfbWFuYWdlciI6dHJ1ZX19LCJwYXJ0bmVyIjpudWxsLCJtZW1iZXIiOm51bGwsImJ1c2luZXNzX21lbWJlciI6bnVsbCwiYWZmaWxpYXRlIjpudWxsLCJsb2dpc3RpY191c2VyIjpudWxsLCJiYW5rX3VzZXIiOm51bGwsInN0cmF0ZWdpY19wYXJ0bmVyX21lbWJlciI6bnVsbCwiYXZhdGFyIjp7InR5cGUiOiJjdXN0b21lciIsInR5cGVfaWQiOjE5MDUwMX0sImV4cCI6MTYyNDM0ODg2OSwic3ViIjoyNjI1MzUsImlzcyI6Imh0dHA6Ly9hY2NvdW50cy5kZXYtc2hlYmEueHl6L2FwaS92My90b2tlbi9nZW5lcmF0ZSIsImlhdCI6MTYyMzc0NDA3MCwibmJmIjoxNjIzNzQ0MDcwLCJqdGkiOiJGcEJvT0V2NGNnekhweThWIn0.gWbCfYkrSfdIdv8GMRz4gFZXDRdIYR5XA_hR3CRMdn8";

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=23.788099544655&lng=90.412001016086', [
            'Authorization' => "Bearer $this->dummyToken"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);

    }

    public function testSProServiceAPIWithoutAuthToken()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=23.788099544655&lng=90.412001016086');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);

    }

    public function testSProServiceAPIWithoutLatLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required.The lng field is required.', $data["message"]);

    }

    public function testSProServiceAPIWithLatFieldAndWithoutLngField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=23.788099544655', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng field is required.', $data["message"]);

    }

    public function testSProServiceAPIWithoutLatFieldAndWithLngField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required.', $data["message"]);

    }

    public function testSProServiceAPIWithInvalidAlphabeticCharacterLatLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=abcde&lng=abcde', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng must be a number.', $data["message"]);

    }

    public function testSProServiceAPIWithInvalidSpecialCharacterLatLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=!@#$%^*&lng=!@#$%^*', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng field is required.', $data["message"]);

    }

    public function testSProServiceAPIWithInvalidSpecialCharacterLatAndValidLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=!@#$%^*&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng field is required.', $data["message"]);

    }

    public function testSProServiceAPIWithInvalidAlphabeticCharacterLatAndValidLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=abcde&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);

    }

    public function testSProServiceAPIWithValidLatAndInvalidAlphabeticCharacterLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=23.788099544655&lng=abcde', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProServiceAPIWithValidLatAndInvalidSpecialCharacterLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories/' . $this->secondaryCategory->id . '/services?lat=23.788099544655&lng=!@#$%^*', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

}
