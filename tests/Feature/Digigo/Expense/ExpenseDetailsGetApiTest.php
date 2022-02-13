<?php

namespace Tests\Feature\Digigo\Expense;

use Carbon\Carbon;
use Database\Factories\ExpensesFactory;
use Sheba\Dal\Expense\Expense;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class ExpenseDetailsGetApiTest extends FeatureTestCase
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

    public function testApiReturnExpenseDetailsAccordingToExpenseId()
    {
        $response = $this->get("/v1/employee/expense/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getUserExpenseFromDatabase($data);
        $this->returnUserExpenseDetailsDataInArrayFormat($data);
    }

    private function getUserExpenseFromDatabase($data)
    {
        /**
         *  All expense Data @return ExpensesFactory
         */
        $this->assertEquals(1, $data['expense']['id']);
        $this->assertEquals(1, $data['expense']['member_id']);
        $this->assertEquals(1, $data['expense']['business_member_id']);
        $this->assertEquals('100.00', $data['expense']['amount']);
        $this->assertEquals('pending', $data['expense']['status']);
        $this->assertEquals(0, $data['expense']['is_updated_by_super_admin']);
        $this->assertEquals('Test Expense', $data['expense']['remarks']);
        $this->assertEquals('other', $data['expense']['type']);
        $this->assertEquals(Carbon::now()->format('M') . ' ' . Carbon::now()->format('d'), $data['expense']['date']);
        $this->assertEquals(Carbon::now()->isoFormat('hh:mm A'), $data['expense']['time']);
        $this->assertEquals(1, $data['expense']['can_edit']);
        $this->assertEquals(null, $data['expense']['reason']);
    }

    private function returnUserExpenseDetailsDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('id', $data['expense']);
        $this->assertArrayHasKey('member_id', $data['expense']);
        $this->assertArrayHasKey('business_member_id', $data['expense']);
        $this->assertArrayHasKey('business_member_id', $data['expense']);
        $this->assertArrayHasKey('status', $data['expense']);
        $this->assertArrayHasKey('is_updated_by_super_admin', $data['expense']);
        $this->assertArrayHasKey('remarks', $data['expense']);
        $this->assertArrayHasKey('type', $data['expense']);
        $this->assertArrayHasKey('created_at', $data['expense']);
        $this->assertArrayHasKey('updated_at', $data['expense']);
        $this->assertArrayHasKey('date', $data['expense']);
        $this->assertArrayHasKey('time', $data['expense']);
        $this->assertArrayHasKey('can_edit', $data['expense']);
        $this->assertArrayHasKey('reason', $data['expense']);
    }
}
