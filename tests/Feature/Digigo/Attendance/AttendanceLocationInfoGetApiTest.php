<?php

namespace Tests\Feature\Digigo\Attendance;

use Database\Factories\BusinessOfficeFactory;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class AttendanceLocationInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Attendance::class, AttendanceSummary::class, AttendanceOverrideLogs::class, AttendanceActionLog::class, BusinessOffice::class]);
        $this->logIn();
        BusinessOffice::factory()->create([
            'business_id' => $this->business->id,
        ]);
        Attendance::factory()->create([
            'business_member_id' => $this->business_member->id,
        ]);
        AttendanceSummary::factory()->create([
            'business_member_id' => $this->business_member->id,
        ]);
        AttendanceActionLog::factory()->create([
            'attendance_id' => 1,
        ]);
        AttendanceOverrideLogs::factory()->create([
            'attendance_id' => 1,
        ]);
    }

    public function testApiReturnEmployeeLocationAddressAccordingToLatLng()
    {
        $response = $this->get("/v1/employee/attendances/info?lat=23.7980928&lng=90.4047646", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        /**
         *  User location info and Ip  Data @return BusinessOfficeFactory
         * also use barikoi API to fetch Location Address according to lat lng
         */
        $this->assertEquals(0, $data['info']['is_in_wifi_area']);
        $this->assertEquals(null, $data['info']['which_office']);
        $this->assertEquals('Sheba.xyz, House 57, Road 25, Block A, Banani, Dhaka', $data['info']['address']);
        $this->assertArrayHasKey('is_in_wifi_area', $data['info']);
        $this->assertArrayHasKey('which_office', $data['info']);
        $this->assertArrayHasKey('address', $data['info']);
    }

    public function testApiReturnUserAddressAccordingToLatLngViaBariKoiApi()
    {
        $response = $this->get("/v1/employee/attendances/info?lat=24.3745&lng=88.6042", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        /**
         *  User location info and Ip  Data @return BusinessOfficeFactory
         * also use barikoi API to fetch Location Address according to lat lng
         */
        $this->assertEquals(0, $data['info']['is_in_wifi_area']);
        $this->assertEquals(null, $data['info']['which_office']);
        $this->assertEquals('Nitol Niloy Filter Industries Limited, Boalia, Rajshahi', $data['info']['address']);
        $this->assertArrayHasKey('is_in_wifi_area', $data['info']);
        $this->assertArrayHasKey('which_office', $data['info']);
        $this->assertArrayHasKey('address', $data['info']);
    }
}
