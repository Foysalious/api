<?php

namespace Tests\Feature\Digigo\Expense;

use Carbon\Carbon;
use Sheba\Dal\Expense\Expense;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class ExpenseListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Expense::class]);
        $this->logIn();
        Expense::factory()->create([
            'member_id' => $this->member->id,
            'business_member_id' => $this->business_member->id
        ]);
    }

    public function testApiReturnExpenseList()
    {
        $response = $this->get("/v1/employee/expense?&limit=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnExpenseListDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/expense?&limit=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
            $this->assertEquals(1, $data['data']['expenses'][0]['id']);
            $this->assertEquals(1, $data['data']['expenses'][0]['member_id']);
            $this->assertEquals('100.00', $data['data']['expenses'][0]['amount']);
            $this->assertEquals('pending', $data['data']['expenses'][0]['status']);
            $this->assertEquals(0, $data['data']['expenses'][0]['is_updated_by_super_admin']);
            $this->assertEquals('Test Expense', $data['data']['expenses'][0]['remarks']);
            $this->assertEquals('other', $data['data']['expenses'][0]['type']);
            $this->assertEquals(100, $data['data']['sum']);
    }

    public function testExpenseListDataApiFormat()
    {
        $response = $this->get("/v1/employee/expense?&limit=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
            $this->assertArrayHasKey('id', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('member_id', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('amount', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('status', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('is_updated_by_super_admin', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('remarks', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('type', $data['data']['expenses'][0]);
            $this->assertArrayHasKey('sum', $data['data']);
    }
}
