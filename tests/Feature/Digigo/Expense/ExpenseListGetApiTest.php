<?php

namespace Tests\Feature\Digigo\Expense;

use Carbon\Carbon;
use Database\Factories\ExpensesFactory;
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

    public function testApiReturnExpenseListFromExpenseTable()
    {
        Expense::factory()->create([
            'member_id' => $this->member->id,
            'business_member_id' => $this->business_member->id,
            'amount' => '200',
            'remarks' => 'Test Expense',
            'type' => 'food',
            'status' => 'pending',
            'is_updated_by_super_admin' => 0,
        ]);
        $response = $this->get("/v1/employee/expense?&limit=10", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getExpenseListDataForExpenseTable($data);
        $this->returnExpenseListDataApiFormat($data);
    }

    private function getExpenseListDataForExpenseTable($data)
    {
        /**
         *  All expense Data @return ExpensesFactory
         */
        $this->assertEquals(1, $data['data']['expenses'][1]['id']);
        $this->assertEquals(1, $data['data']['expenses'][1]['member_id']);
        $this->assertEquals('100.00', $data['data']['expenses'][1]['amount']);
        $this->assertEquals('pending', $data['data']['expenses'][1]['status']);
        $this->assertEquals(0, $data['data']['expenses'][1]['is_updated_by_super_admin']);
        $this->assertEquals('Test Expense', $data['data']['expenses'][1]['remarks']);
        $this->assertEquals('other', $data['data']['expenses'][1]['type']);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['data']['expenses'][1]['created_at'])->format('Y-m-d H:i'));
        $this->assertEquals(2, $data['data']['expenses'][0]['id']);
        $this->assertEquals(1, $data['data']['expenses'][0]['member_id']);
        $this->assertEquals('200.00', $data['data']['expenses'][0]['amount']);
        $this->assertEquals('pending', $data['data']['expenses'][0]['status']);
        $this->assertEquals(0, $data['data']['expenses'][0]['is_updated_by_super_admin']);
        $this->assertEquals('Test Expense', $data['data']['expenses'][0]['remarks']);
        $this->assertEquals('food', $data['data']['expenses'][0]['type']);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['data']['expenses'][0]['created_at'])->format('Y-m-d H:i'));

        /**
         * expense sum calculate based on specific business member created total expense @return ExpensesFactory
         */
        $this->assertEquals(300, $data['data']['sum']);
    }

    private function returnExpenseListDataApiFormat($data)
    {
        foreach ($data['data']['expenses'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('member_id', $item);
            $this->assertArrayHasKey('amount', $item);
            $this->assertArrayHasKey('status', $item);
            $this->assertArrayHasKey('is_updated_by_super_admin', $item);
            $this->assertArrayHasKey('remarks', $item);
            $this->assertArrayHasKey('type', $item);
        }
        $this->assertArrayHasKey('sum', $data['data']);
    }
}
