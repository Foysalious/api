<?php

namespace Tests\Feature\Digigo\Support;

use Carbon\Carbon;
use Sheba\Dal\Support\Model as Support;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class TicketListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Support::class]);
        $this->logIn();
        Support::factory()->create();
    }

    public function testApiReturnSupportListAccordingToLimitParams()
    {
        $response = $this->get("/v1/employee/supports?status=open&limit=5&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/supports?status=open&limit=5&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['supports'] as $item) {
            $this->assertEquals(1,$item['id']);
            $this->assertEquals(1,$item['member_id']);
            $this->assertEquals('open',$item['status']);
            $this->assertEquals('Test Ticket',$item['long_description']);
            $this->assertEquals(Carbon::now()->format('M d'),$item['date']);
            $this->assertEquals(Carbon::now()->format('h:i A'),$item['time']);
        }
    }

    public function testEmployeeSupportTicketListDataApiFormat()
    {
        $response = $this->get("/v1/employee/supports?status=open&limit=5&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['supports'] as $item) {
            $this->assertArrayHasKey('id',$item);
            $this->assertArrayHasKey('member_id',$item);
            $this->assertArrayHasKey('status',$item);
            $this->assertArrayHasKey('long_description',$item);
            $this->assertArrayHasKey('created_at',$item);
            $this->assertArrayHasKey('date',$item);
            $this->assertArrayHasKey('time',$item);
        }
    }
}
