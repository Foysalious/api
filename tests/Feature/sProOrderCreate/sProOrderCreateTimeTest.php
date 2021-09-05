<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\DB;
use Tests\Feature\FeatureTestCase;

class sProOrderCreateTimeTest extends FeatureTestCase
{

    private $schedule_slot;

    public function setUp()
    {
        parent::setUp();

        DB::table('schedule_slots')->delete();

        $this->logIn();

        $this->truncateTable(ScheduleSlot::class);

        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['00:00:00', '01:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['01:00:00', '02:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['02:00:00', '03:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['03:00:00', '04:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['04:00:00', '05:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['05:00:00', '06:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['06:00:00', '07:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['07:00:00', '08:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['08:00:00', '09:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['09:00:00', '10:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['10:00:00', '11:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['11:00:00', '12:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['12:00:00', '13:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['13:00:00', '14:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['14:00:00', '15:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['15:00:00', '16:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['16:00:00', '17:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['17:00:00', '18:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['18:00:00', '19:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['19:00:00', '20:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['20:00:00', '21:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['21:00:00', '22:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['22:00:00', '23:00:00']);
        DB::insert('insert into schedule_slots(start,end) values (?, ?)', ['23:00:00', '24:00:00']);


    }

    public function testSProTimeAPIWithValidPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category=2&partner=1&limit=14');

        $data = $response->decodeResponseJson();
        dd($data);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(2, $data["categories"][0]["id"]);
        $this->assertEquals(2, $data["categories"][0]["name"]);
        $this->assertEquals(2, $data["categories"][0]["bn_name"]);
        $this->assertEquals(2, $data["categories"][0]["is_vat_applicable"]);
        $this->assertEquals(2, $data["categories"][0]["is_car_rental"]);
        $this->assertEquals(2, $data["categories"][0]["vat_percentage"]);

    }

}
