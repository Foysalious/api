<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\PartnerResource;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

class sProOrderCreateCategoryTest extends FeatureTestCase
{
    private $categoryPartner;
    private $locationService;
    private $service;
    private $master_category;
    private $categoryLocation;

    public function setUp()
    {

        parent::setUp();

        $this->truncateTable(Category::class);

        $this->truncateTable(Service::class);

        $this->truncateTable(LocationService::class);

        $this->truncateTable(CategoryPartner::class);

        $this->truncateTable(CategoryLocation::class);

        $this->logIn();

        $this->master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => 'Car Wash',
            'bn_name' => 'গাড়ী ধোয়া',
            'parent_id' => $this->master_category->id,
            'publication_status' => 1
        ]);

        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1
        ]);

        $this->partner -> update([
            'geo_informations' => '{"lat":"23.788099544655","lng":"90.412001016086","radius":"500"}'
        ]);

        $this->locationService = factory(LocationService::class)->create();

        $this->categoryPartner = factory(CategoryPartner::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'partner_id' => $this->partner->id,
            'min_order_amount' => 0.00,
            'is_home_delivery_applied' => 1,
            'is_partner_premise_applied' => 0,
            'uses_sheba_logistic' => 0,
            'delivery_charge' => 0.00,
            'preparation_time_minutes' => 0
        ]);

        $this->categoryLocation = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

    }

    public function testSProCategoryAPIWithValidLatLng()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(2, $data["categories"][0]["id"]);
        $this->assertEquals('Car Wash', $data["categories"][0]["name"]);
        $this->assertEquals('গাড়ী ধোয়া', $data["categories"][0]["bn_name"]);
        $this->assertEquals(0, $data["categories"][0]["is_vat_applicable"]);
        $this->assertEquals(0, $data["categories"][0]["is_car_rental"]);
        $this->assertEquals(5, $data["categories"][0]["vat_percentage"]);

    }

    public function testSProCategoryAPIWithInvalidAuthToken()
    {
        //arrange
        $dummyToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiR2VyYWxkIEhlcnpvZyIsImltYWdlIjpudWxsLCJwcm9maWxlIjp7ImlkIjoxLCJuYW1lIjoiR2VyYWxkIEhlcnpvZyIsImVtYWlsX3ZlcmlmaWVkIjoxfSwiY3VzdG9tZXIiOnsiaWQiOjF9LCJyZXNvdXJjZSI6eyJpZCI6MSwicGFydG5lciI6eyJpZCI6MSwibmFtZSI6IlJlaWxseSBIaWxscyBNRCIsInN1Yl9kb21haW4iOm51bGwsImxvZ28iOm51bGwsImlzX21hbmFnZXIiOnRydWV9fSwibWVtYmVyIjp7ImlkIjoxfSwiYnVzaW5lc3NfbWVtYmVyIjp7ImlkIjoxLCJidXNpbmVzc19pZCI6MSwibWVtYmVyX2lkIjoxLCJpc19zdXBlciI6MX0sImFmZmlsaWF0ZSI6eyJpZCI6MX0sImxvZ2lzdGljX3VzZXIiOm51bGwsImJhbmtfdXNlciI6bnVsbCwic3RyYXRlZ2ljX3BhcnRuZXJfbWVtYmVyIjpudWxsLCJhdmF0YXIiOm51bGwsImV4cCI6MTYzMDU1OTAxMywic3ViIjoxLCJpc3MiOiJodHRwOi8vYXBpLnNoZWJhLnRlc3QiLCJpYXQiOjE2MzA0NzI2MTMsIm5iZiI6MTYzMDQ3MjYxMywianRpIjoiZ0hjcHFPRHEwS0tBOFBMZyJ9.FLdpOzoVO0MivgmlPYrNt8ccN_RkntxG98Abttj0PUD";

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=90.412001016086', [
            'Authorization' => "Bearer $dummyToken"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);

    }

    public function testSProCategoryAPIWithoutAuthToken()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=90.412001016086');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);

    }

    public function testSProCategoryAPIWithoutLatLngField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required.The lng field is required.', $data["message"]);

    }

    public function testSProCategoryAPIWithoutLngField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng field is required.', $data["message"]);

    }

    public function testSProCategoryAPIWithoutLatField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required.', $data["message"]);

    }

    public function testSProCategoryAPIWithInvalidCharacterLatField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=abcde&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);

    }

    public function testSProCategoryAPIWithInvalidSpecialCharacterLatField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=!@%$^%&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);

    }

    public function testSProCategoryAPIWithInvalidCharacterLngField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=abcde', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProCategoryAPIWithInvalidSpecialCharacterLngField()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=!@%$^%&', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProCategoryAPIWithMultipleCategory()
    {
        //arrange
        $this->secondaryCategory = factory(Category::class)->create([
            'name' => 'Car Clean',
            'bn_name' => 'গাড়ী পরিষ্কার',
            'parent_id' => $this->master_category->id,
            'publication_status' => 1
        ]);

        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1
        ]);

        $this->locationService = factory(LocationService::class)->create([
            'location_id' => 4,
            'service_id' => $this->service->id
        ]);

        $this->categoryPartner = factory(CategoryPartner::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'partner_id' => $this->partner->id,
            'min_order_amount' => 0.00,
            'is_home_delivery_applied' => 1,
            'is_partner_premise_applied' => 0,
            'uses_sheba_logistic' => 0,
            'delivery_charge' => 0.00,
            'preparation_time_minutes' => 0
        ]);

        $this->categoryLocation = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(2, $data["categories"][0]["id"]);
        $this->assertEquals('Car Wash', $data["categories"][0]["name"]);
        $this->assertEquals('গাড়ী ধোয়া', $data["categories"][0]["bn_name"]);
        $this->assertEquals(0, $data["categories"][0]["is_vat_applicable"]);
        $this->assertEquals(0, $data["categories"][0]["is_car_rental"]);
        $this->assertEquals(5, $data["categories"][0]["vat_percentage"]);
        $this->assertEquals(3, $data["categories"][1]["id"]);
        $this->assertEquals('Car Clean', $data["categories"][1]["name"]);
        $this->assertEquals('গাড়ী পরিষ্কার', $data["categories"][1]["bn_name"]);
        $this->assertEquals(0, $data["categories"][1]["is_vat_applicable"]);
        $this->assertEquals(0, $data["categories"][1]["is_car_rental"]);
        $this->assertEquals(5, $data["categories"][1]["vat_percentage"]);

    }

    public function testSProCategoryAPIWithFivePercentVat()
    {
        //arrange
        $this->secondaryCategory -> update([
            'is_vat_applicable' => 5
        ]);

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.788099544655&lng=90.412001016086', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(2, $data["categories"][0]["id"]);
        $this->assertEquals('Car Wash', $data["categories"][0]["name"]);
        $this->assertEquals('গাড়ী ধোয়া', $data["categories"][0]["bn_name"]);
        $this->assertEquals(5, $data["categories"][0]["is_vat_applicable"]);
        $this->assertEquals(0, $data["categories"][0]["is_car_rental"]);
        $this->assertEquals(5, $data["categories"][0]["vat_percentage"]);

    }

}
