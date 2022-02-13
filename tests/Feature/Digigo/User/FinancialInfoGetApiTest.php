<?php

namespace Tests\Feature\Digigo\User;

use App\Models\Member;
use App\Models\Profile;
use App\Models\ProfileBankInformation;
use Database\Factories\ProfileBankInformationFactory;
use Database\Factories\ProfileFactory;
use Illuminate\Support\Facades\DB;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class FinancialInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([ProfileBankInformation::class]);
        $this->logIn();
        ProfileBankInformation::factory()->create([
            "profile_id" => $this->profile->id,
        ]);
    }

    public function testApiReturnUserFinancialInformationFromDatabase()
    {
        Member::find(1)->update([
            "bank_account" => '12345678910',
        ]);
        Profile::find(1)->update([
            "tin_no" => '12345678910',
            "tin_certificate" => 'Khairun Nahar',
        ]);
        $response = $this->get("/v1/employee/profile/1/financial", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getUserOfficialDataFromDatabase($data);
        $this->returnUserOfficialDataInArrayFormat($data);
    }

    private function getUserOfficialDataFromDatabase($data)
    {
        /**
         *  Bank and Account no  @return ProfileBankInformationFactory
         */
        $this->assertEquals('City Bank', $data['financial_info']['bank_name']);
        $this->assertEquals('12345678910', $data['financial_info']['account_no']);

        /**
         *  Tin info @return ProfileFactory
         */
        $this->assertEquals('12345678910', $data['financial_info']['tin_no']);
        $this->assertEquals('Khairun Nahar', $data['financial_info']['tin_certificate_name']);
        $this->assertEquals('Khairun Nahar', $data['financial_info']['tin_certificate']);
    }


    private function returnUserOfficialDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('bank_name', $data['financial_info']);
        $this->assertArrayHasKey('account_no', $data['financial_info']);
        $this->assertArrayHasKey('tin_no', $data['financial_info']);
        $this->assertArrayHasKey('tin_certificate_name', $data['financial_info']);
        $this->assertArrayHasKey('tin_certificate', $data['financial_info']);
    }
}
