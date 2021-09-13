<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\PartnerResource;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryScheduleSlot\CategoryScheduleSlot;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

class sProOrderCreateScheduleTest extends FeatureTestCase
{
    private $today;
    private $dummyToken;

    public function setUp()
    {

        parent::setUp();

        $this->today = Carbon::now()->toDateString();

        DB::table('category_partner_resource')->truncate();

        $this->truncateTable(Service::class);

        $this->truncateTable(Category::class);

        $this->truncateTable(ScheduleSlot::class);

        $this->truncateTable(CategoryScheduleSlot::class);

        $this->truncateTable(PartnerResource::class);
    
        $this->logIn();

        $this->partner_resource->update([
            'resource_type' => 'Handyman'
        ]);

        $master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $master_category->id,
            'publication_status' => 1
        ]);

        factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1
        ]);

        for ($x = 0; $x < 24; $x++) {

            $y = $x + 1;

            $this->scheduleSlot1 = factory(ScheduleSlot::class)->create([
                'start' => "$x:00:00",
                'end' => "$y:00:00",
            ]);

            if($x > 7 && $x < 22){

                for($z = 0; $z < 7; $z++){
                    DB::insert('insert into category_schedule_slot(category_id,schedule_slot_id,day) values (?, ?, ?)', [$this->secondaryCategory->id, $this->scheduleSlot1->id, $z]);
                }
            }
        }

        DB::insert('insert into category_partner_resource(partner_resource_id,category_id) values (?, ?)', [1, 2]);

    }

    public function testSProScheduleAPIWithValidCategoryIdPartnerIdDateTime()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&date=' . $this->today . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->today, $data["schedule"]["date"]);
        $this->assertEquals(16, $data["schedule"]["slot"]["id"]);

    }

    public function testSProScheduleAPIWithNoAuthToken()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&date=' . $this->today . '&time=15:00:00-16:00:00');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);
    }

    public function testSProScheduleAPIWithInvalidAuthToken()
    {
        //arrange

        $this->dummyToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImltYWdlIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9yZXNvdXJjZXMvYXZhdGFyLzE2MjI1MjA3NDNfa2F6aWZhaGR6YWt3YW4uanBnIiwicHJvZmlsZSI6eyJpZCI6MjYyNTM1LCJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImVtYWlsX3ZlcmlmaWVkIjowfSwiY3VzdG9tZXIiOnsiaWQiOjE5MDUwMX0sInJlc291cmNlIjp7ImlkIjo0NjMzMSwicGFydG5lciI6eyJpZCI6MjE2NzA0LCJuYW1lIjoiIiwic3ViX2RvbWFpbiI6InNlcnZpY2luZy1iZCIsImxvZ28iOiJodHRwczovL3MzLmFwLXNvdXRoLTEuYW1hem9uYXdzLmNvbS9jZG4tc2hlYmFkZXYvaW1hZ2VzL3BhcnRuZXJzL2xvZ29zLzE2MjI0NDM4ODBfc2VydmljaW5nYmQucG5nIiwiaXNfbWFuYWdlciI6dHJ1ZX19LCJwYXJ0bmVyIjpudWxsLCJtZW1iZXIiOm51bGwsImJ1c2luZXNzX21lbWJlciI6bnVsbCwiYWZmaWxpYXRlIjpudWxsLCJsb2dpc3RpY191c2VyIjpudWxsLCJiYW5rX3VzZXIiOm51bGwsInN0cmF0ZWdpY19wYXJ0bmVyX21lbWJlciI6bnVsbCwiYXZhdGFyIjp7InR5cGUiOiJjdXN0b21lciIsInR5cGVfaWQiOjE5MDUwMX0sImV4cCI6MTYyNDM0ODg2OSwic3ViIjoyNjI1MzUsImlzcyI6Imh0dHA6Ly9hY2NvdW50cy5kZXYtc2hlYmEueHl6L2FwaS92My90b2tlbi9nZW5lcmF0ZSIsImlhdCI6MTYyMzc0NDA3MCwibmJmIjoxNjIzNzQ0MDcwLCJqdGkiOiJGcEJvT0V2NGNnekhweThWIn0.gWbCfYkrSfdIdv8GMRz4gFZXDRdIYR5XA_hR3CRMdn8";

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&date=' . $this->today . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->dummyToken"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);
    }

    public function testSProScheduleAPIWithNoCategoryId()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?partner=' . $this->partner->id . '&date=' . $this->today . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The category field is required.', $data["message"]);
    }

    public function testSProScheduleAPIWithInvalidCharacterCategoryId()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category=abcde&partner=' . $this->partner->id . '&date=' . $this->today . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The category must be a number.', $data["message"]);
    }

    public function testSProScheduleAPIWithNoPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&date=' . $this->today . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The partner field is required.', $data["message"]);
    }

    public function testSProScheduleAPIWithInvalidPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=abcde&date=' . $this->today . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The partner must be a number.', $data["message"]);
    }

    public function testSProScheduleAPIWithNoDate()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The date field is required.', $data["message"]);
    }

    public function testSProScheduleAPIWithInvalidDate()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&date=abcde&time=15:00:00-16:00:00', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The date does not match the format Y-m-d.', $data["message"]);

    }

    public function testSProScheduleAPIWithNoTime()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&date=' . $this->today, [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The time field is required.', $data["message"]);
    }

    public function testSProScheduleAPIWithInvalidTime()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/schedules/check?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&date=' . $this->today . '&time=abcde', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Schedule not found.', $data["message"]);
    }

}
