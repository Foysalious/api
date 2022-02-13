<?php

namespace Tests\Feature\Digigo\Login;

use App\Models\BusinessMember;
use App\Models\Profile;
use Sheba\Dal\BusinessAttendanceTypes\Model as BusinessAttendanceTypes;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockAccountServerClient;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class DigigoLoginPostApiTest extends FeatureTestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testApiShouldReturnBusinessMemberInfoAfterLoginIntoSystem()
    {
        $this->truncateTables([BusinessAttendanceTypes::class]);
        $this->logIn();
        BusinessAttendanceTypes::factory()->create([
            'business_id' => $this->business->id,
        ]);
        MockAccountServerClient::$token = $this->token;
        $response = $this->post('/v1/employee/login', [
            'email' => 'tisha@sheba.xyz', 'password' => '12345'
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals($this->token, $data['token']);
        /**
         * Mobile and Image info @return Profile
         */
        $this->assertEquals("+8801678242955", $data['user']['mobile']);
        $this->assertEquals("https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg", $data['user']['image']);
        /**
         *  business_id, business_name @return BusinessMember
         */
        $this->assertEquals(1, $data['user']['business_id']);
        $this->assertEquals("My Company", $data['user']['business_name']);
        /**
         *  is_remote_attendance_enable @return BusinessAttendanceTypes
         */
        $this->assertEquals(false, $data['user']['is_remote_attendance_enable']);
    }

    public function testApiShouldReturnRemoteAttendanceEnableTrueIfAttendanceTypeConfigureRemote()
    {
        $this->truncateTables([BusinessAttendanceTypes::class]);
        $this->logIn();
        BusinessAttendanceTypes::factory()->create([
            'business_id' => $this->business->id,
        ]);
        BusinessAttendanceTypes::find(1)->update(["attendance_type" => 'remote']);
        MockAccountServerClient::$token = $this->token;
        $response = $this->post('/v1/employee/login', [
            'email' => 'tisha@sheba.xyz', 'password' => '12345'
        ]);
        $data = $response->json();
        /**
         *  is_remote_attendance_enable @return BusinessAttendanceTypes
         */
        $this->assertEquals(true, $data['user']['is_remote_attendance_enable']);
    }
}
