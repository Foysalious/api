<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryScheduleSlot\CategoryScheduleSlot;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;
use Illuminate\Support\Facades\DB;

class sProOrderCreateTimeTest extends FeatureTestCase
{

    private $scheduleSlot1;
    private $today;

    public function setUp()
    {

        parent::setUp();

        $this->truncateTable(Service::class);

        $this->truncateTable(Category::class);

        $this->truncateTable(ScheduleSlot::class);

        $this->truncateTable(CategoryScheduleSlot::class);

        $this->logIn();

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

    }

    public function testSProTimeAPIWithValidCategoryIdPartnerIdLimit()
    {
        //arrange
        $this->today = Carbon::now()->toDateString();

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->today, $data["dates"][0]["value"]);
        $this->assertEquals(9, $data["dates"][0]["slots"][0]["id"]);

    }

    public function testSProTimeAPIWithValidCategoryIdPartnerIdAndInvalidAlphabeticCharacterLimit()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&limit=abcde');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The limit must be a number.', $data["message"]);

    }

    public function testSProTimeAPIWithValidCategoryIdPartnerIdAndInvalidSpecialCharacterLimit()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id . '&limit=!@#$%^');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The limit must be a number.', $data["message"]);

    }

    public function testSProTimeAPIWithValidCategoryIdPartnerIdAndNoLimit()
    {
        //arrange
        $this->today = Carbon::now()->toDateString();

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&partner=' . $this->partner->id);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->today, $data["dates"][0]["value"]);
        $this->assertEquals(9, $data["dates"][0]["slots"][0]["id"]);

    }

    public function testSProTimeAPIWithValidCategoryIdLimitAndInvalidAlphabeticCharacterPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&partner=abcde&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The partner must be a number.', $data["message"]);

    }

    public function testSProTimeAPIWithValidCategoryIdLimitAndInvalidSpecialCharacterPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&partner=!@#$%^&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The partner must be a number.', $data["message"]);

    }

    public function testSProTimeAPIWithValidCategoryIdLimitAndNoPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id . '&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertNotEmpty($data["times"]);
        $this->assertNotEmpty($data["valid_times"]);

    }

    public function testSProTimeAPIWithValidPartnerIdLimitAndInvalidAlphabeticCharacterCategoryId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category=abcde&partner=' . $this->partner->id . '&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The category must be a number.', $data["message"]);

    }

    public function testSProTimeAPIWithValidPartnerIdLimitAndInvalidSpecialCharacterCategoryId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category=!@#$%^&partner=' . $this->partner->id . '&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The category must be a number.', $data["message"]);

    }

    public function testSProTimeAPIWithValidPartnerIdLimitAndNoCategoryId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?partner=' . $this->partner->id . '&limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertNotEmpty($data["times"]);
        $this->assertNotEmpty($data["valid_times"]);

    }

    public function testSProTimeAPIWithNoPartnerIdLimitCategoryId()
    {
        //arrange

        //act
        $response = $this->get('/v2/times');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertNotEmpty($data["times"]);
        $this->assertNotEmpty($data["valid_times"]);

    }

    public function testSProTimeAPIWithOnlyValidCategoryIdParameter()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?category='. $this->secondaryCategory->id);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertNotEmpty($data["times"]);
        $this->assertNotEmpty($data["valid_times"]);

    }

    public function testSProTimeAPIWithOnlyValidPartnerIdParameter()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?partner=' . $this->partner->id);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertNotEmpty($data["times"]);
        $this->assertNotEmpty($data["valid_times"]);

    }

    public function testSProTimeAPIWithOnlyValidLimitParameter()
    {
        //arrange

        //act
        $response = $this->get('/v2/times?limit=1');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertNotEmpty($data["times"]);
        $this->assertNotEmpty($data["valid_times"]);

    }

}
