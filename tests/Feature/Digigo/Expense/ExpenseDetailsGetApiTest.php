<?php

namespace Tests\Feature\Digigo\Expense;

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

    public function testApiReturnExpenseDetailsWithValidExpenseId()
    {
        $response = $this->get("/v1/employee/expense/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
