<?php namespace Tests\Feature\CustomerOrder;

use App\Models\ScheduleSlot;
use Tests\Feature\FeatureTestCase;
use Carbon\Carbon;

/**
 * @author Mahanaz Tabassum <mahanaz.tabassum@sheba.xyz>
 */
class ScheduleSlotTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp(); 
        $this->truncateTable(ScheduleSlot::class);
    }

    public function testAPIResponse200()
    {
        // arrange
        ScheduleSlot::create([
            "start"=>"06:00:00",
            "end"=>"07:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"07:00:00",
            "end"=>"08:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"08:00:00",
            "end"=>"09:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"09:00:00",
            "end"=>"10:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"11:00:00",
            "end"=>"12:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"12:00:00",
            "end"=>"13:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"13:00:00",
            "end"=>"14:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"14:00:00",
            "end"=>"15:00:00"
        ]);

        // act
        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        // assert
        $this->assertEquals(200,$data["code"]);
    }

    public function testInvalidCategoryShouldReturn404()
    {
        $response = $this->get("/v3/times?category=9869");
        $data = $response->decodeResponseJson();

        $this->assertEquals(404,$data["code"]);
        $this->assertEquals("Category does not exists",$data["message"]);
    }

    public function testInvalidPartnerShouldReturn404()
    {
        $response = $this->get("/v3/times?partner=0");
        $data = $response->decodeResponseJson();

        $this->assertEquals(404,$data["code"]);
        $this->assertEquals("Partner does not exists",$data["message"]);
    }

    public function testInvalidLimitShouldReturnValidationException()
    {
        $response = $this->get("/v3/times?limit=0");
        $data = $response->decodeResponseJson();

        $this->assertEquals(400,$data["code"]);
        $this->assertEquals("The limit must be at least 1.",$data["message"]);
    }

    public function testGivenNoLimitItShouldReturnOneDay()
    {
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $this->assertEquals(200,$data["code"]);
        $this->assertEquals(1, count($data["dates"]));
    }

    public function testGivenNoLimitItShouldReturnToday()
    {
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $todayDate = Carbon::now()->toDateString();
        $this->assertEquals($todayDate, $data["dates"][0]["value"]);
    }

    public function testGivenALimitItShouldReturnThatManyDays()
    {
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times?limit=5");
        $data = $response->decodeResponseJson();

        $this->assertEquals(200,$data["code"]);
        $this->assertEquals(5, count($data["dates"]));
    }

    public function testGivenALimitItShouldReturnThatManyDaysFromTodaySequentially()
    {
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times?limit=5");
        $data = $response->decodeResponseJson();

        $this->assertEquals(Carbon::now()->toDateString(), $data["dates"][0]["value"]);
        $this->assertEquals(Carbon::now()->addDay()->toDateString(), $data["dates"][1]["value"]);
        $this->assertEquals(Carbon::now()->addDays(2)->toDateString(), $data["dates"][2]["value"]);
        $this->assertEquals(Carbon::now()->addDays(3)->toDateString(), $data["dates"][3]["value"]);
        $this->assertEquals(Carbon::now()->addDays(4)->toDateString(), $data["dates"][4]["value"]);
    }

    public function testInvalidForShouldReturnValidationException()
    {
        $response = $this->get("/v3/times?for=managersssss");
        $data = $response->decodeResponseJson();

        $this->assertEquals(400,$data["code"]);
        $this->assertEquals("The selected for is invalid.",$data["message"]);
    }

    public function testValidIfForIsCustomer()
    {
        $response = $this->get("/v3/times?for=customer");
        $data = $response->decodeResponseJson();

        $this->assertEquals(200,$data["code"]);
        $this->assertEquals("Successful",$data["message"]);
    }

    public function testValidIfForIsManager()
    {
        $response = $this->get("/v3/times?for=manager");
        $data = $response->decodeResponseJson();

        $this->assertEquals(200,$data["code"]);
        $this->assertEquals("Successful",$data["message"]);
    }

    public function testScheduleSlotShouldBeEmptyIfThereIsNoEntry()
    {
        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $this->assertEquals(0, count($data["dates"]));
    }

    public function testScheduleSlotCountShouldBeEqualToEntry()
    {
        ScheduleSlot::create([
            "start"=>"09:00:00",
            "end"=>"10:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $this->assertEquals(2, count($data["dates"][0]["slots"]));
    }

    public function testScheduleSlotShouldBeBetween9AmTo8PM()
    {
        ScheduleSlot::create([
            "start"=>"08:00:00",
            "end"=>"09:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"09:00:00",
            "end"=>"10:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"11:00:00",
            "end"=>"12:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"13:00:00",
            "end"=>"14:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"21:00:00",
            "end"=>"22:00:00"
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $this->assertEquals(4, count($data["dates"][0]["slots"]));
    }

    public function testScheduleSlotDataFormat()
    {
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $slot = $data["dates"][0]["slots"][0];
        $this->assertTrue(array_key_exists("start",$slot));
        $this->assertTrue(array_key_exists("end",$slot));
        $this->assertTrue(array_key_exists("key",$slot));
        $this->assertTrue(array_key_exists("value",$slot));
        $this->assertTrue(array_key_exists("is_valid",$slot));
        $this->assertTrue(array_key_exists("is_available",$slot));
        $this->assertEquals("10:00 AM", $slot["start"]);
        $this->assertEquals("11:00 AM", $slot["end"]);
        $this->assertEquals("10:00:00-11:00:00", $slot["key"]);
        $this->assertEquals("10 - 11 AM", $slot["value"]);
    }

    public function testScheduleSlotShouldBeEqualToEntry()
    {
        ScheduleSlot::create([
            "start"=>"09:00:00",
            "end"=>"10:00:00"
        ]);
        ScheduleSlot::create([
            "start"=>"10:00:00",
            "end"=>"11:00:00"
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $slots = $data["dates"][0]["slots"];
        $this->assertEquals("09:00:00-10:00:00", $slots[0]["key"]);
        $this->assertEquals("10:00:00-11:00:00", $slots[1]["key"]);
    }

    public function testOnlyFutureSlotIsValid()
    {
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->subHour()->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->toTimeString()
        ]);
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->addHour()->toTimeString()
        ]);
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->addHour()->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->addHours(2)->toTimeString()
        ]);

        $response = $this->get("/v3/times?limit=2");
        $data = $response->decodeResponseJson();

        $dates = $data["dates"];

        if(count($dates[0]["slots"] ) > 0) {
            $this->assertEquals(0, $dates[0]["slots"][0]["is_valid"]);
        }
        if(count($dates[0]["slots"] ) > 1){
            $this->assertEquals(0, $dates[0]["slots"][1]["is_valid"]);
        }
        if(count($dates[0]["slots"] ) > 2){
            $this->assertEquals(1, $dates[0]["slots"][2]["is_valid"]);
        }
        if(count($dates[1]["slots"] ) > 0) {
            $this->assertEquals(1, $dates[1]["slots"][0]["is_valid"]);
        }
        if(count($dates[1]["slots"] ) > 1) {
            $this->assertEquals(1, $dates[1]["slots"][1]["is_valid"]);
        }
        if(count($dates[1]["slots"] ) > 2) {
            $this->assertEquals(1, $dates[1]["slots"][2]["is_valid"]);
        }
    }

    public function testPastSlotAndCurrentSlotIsInvalid()
    {
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->subHours(2)->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->subHour()->toTimeString()
        ]);
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->subHour()->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->toTimeString()
        ]);
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->addHour()->toTimeString()
        ]);
        ScheduleSlot::create([
            "start"=>Carbon::now()->startOfHour()->addHour()->toTimeString(),
            "end"=>Carbon::now()->startOfHour()->addHours(2)->toTimeString()
        ]);

        $response = $this->get("/v3/times");
        $data = $response->decodeResponseJson();

        $slots = $data["dates"][0]["slots"];

        if(count($slots ) > 0) {
            $this->assertEquals(0, $slots[0]["is_valid"]);
        }
        if(count($slots ) > 1){
            $this->assertEquals(0, $slots[1]["is_valid"]);
        }
        if(count($slots ) > 2){
            $this->assertEquals(0, $slots[2]["is_valid"]);
        }
        if(count($slots ) > 3) {
            $this->assertEquals(1, $slots[3]["is_valid"]);
        }
    }
}
