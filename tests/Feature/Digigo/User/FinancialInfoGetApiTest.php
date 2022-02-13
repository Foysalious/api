<?php

namespace Tests\Feature\Digigo\User;

use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class FinancialInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnUserFinancialInformation()
    {
        $response = $this->get("/v1/employee/profile/1/financial", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/profile/1/financial", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(null, $data['financial_info']['bank_name']);
        $this->assertEquals(null, $data['financial_info']['account_no']);
        $this->assertEquals(null, $data['financial_info']['tin_no']);
        $this->assertEquals(null, $data['financial_info']['tin_certificate_name']);
        $this->assertEquals(null, $data['financial_info']['tin_certificate']);
    }

    public function testFinancialInfoDataApiFormat()
    {
        $response = $this->get("/v1/employee/profile/1/financial", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('bank_name', $data['financial_info']);
        $this->assertArrayHasKey('account_no', $data['financial_info']);
        $this->assertArrayHasKey('tin_no', $data['financial_info']);
        $this->assertArrayHasKey('tin_certificate_name', $data['financial_info']);
        $this->assertArrayHasKey('tin_certificate', $data['financial_info']);
    }
}
