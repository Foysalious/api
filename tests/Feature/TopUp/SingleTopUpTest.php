<?php

namespace Tests\Feature\TopUp;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Profile;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;
use Sheba\Dal\TopUpBlockedAgent\TopUpBlockedAgent;
use Sheba\Dal\TopUpBlockedAgentLog\TopUpBlockedAgentLog;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;
use Throwable;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class SingleTopUpTest extends FeatureTestCase
{
    /** @var $topUpVendor */
    private $topUpVendor;
    /** @var $topUpVendorCommission */
    private $topUpVendorCommission;
    /** @var $topUpOtfSettings */
    private $topUpOtfSettings;
    /** @var $topUpVendorOtf */
    private $topUpVendorOtf;
    /** @var $topUpStatusChangeLog */
    private $topUpStatusChangeLog;
    /** @var $topBlocklistNumbers */
    private $topBlocklistNumbers;

    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([
            TopUpVendor::class,
            TopUpVendorCommission::class,
            TopUpOTFSettings::class,
            TopUpOrder::class,
            TopUpBlacklistNumber::class,
            AffiliateTransaction::class,
            Profile::class,
            Affiliate::class,
            TopUpBlockedAgent::class,
            TopUpBlockedAgentLog::class,
        ]);
        $this->logIn();

        $this->topUpVendor = TopUpVendor::factory()->create();
        $this->topUpVendorCommission = TopUpVendorCommission::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id,
        ]);

        $this->topUpOtfSettings = TopUpOTFSettings::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id,
        ]);

        $this->topUpVendorOtf = TopUpVendorOTF::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id,
        ]);

        $this->topUpStatusChangeLog = TopUpVendorOTFChangeLog::factory()->create([
            'otf_id' => $this->topUpVendorOtf->id,
        ]);

        TopUpBlacklistNumber::factory()->create();
    }

    public function testInvalidMobileNumberIsRejected()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '016782429559',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testTopupConsecutiveNumber()
    {
        TopUpVendor::find(1)->update(["waiting_time" => 1]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("?????? ???????????????????????? ???????????????????????? ????????? ??????-?????? ????????? ?????????????????? ??? ????????????????????? ?????? ???????????????????????? ??????-?????? ???????????? ???????????? ??????????????????????????????????????? 1 ??????????????? ????????????????????? ???????????? ???",
            $data['message']
        );
    }

    public function testTopupUserBlockedIfTryToTopupConsecutivelyForFiveTimes()
    {
        TopUpBlockedAgent::factory()->create([
            'agent_id' => 1,
            'agent_type' => 'App\Models\Affiliate'
        ]);

        TopUpBlockedAgentLog::factory()->create([
            'agent_id' => 1,
            'agent_type' => 'App\Models\Affiliate'
        ]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011015',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011018',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011011',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801620011012',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(429, $data['code']);
        $this->assertEquals('You have been blocked to do top up. Please contact customer care.', $data['message']
        );
    }

    public function testMobileNumberValidationResponseCode()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testVendorIdValidationResponseCode()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testConnectionTypeValidationResponseCode()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testAmountValidationResponseCode()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testPasswordValidationResponseCode()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testOneTopUpRequestCreateOneTopUpOrder()
    {
        $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $this->assertEquals(1, TopUpOrder::count());
    }

    public function testTopUpOrderDataMatchesTopUpRequestData()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals(1, $top_up_order->id);
        $this->assertEquals('Successful', $top_up_order->status);
        $this->assertEquals('+8801678242955', $top_up_order->payee_mobile);
        $this->assertEquals('prepaid', $top_up_order->payee_mobile_type);
        $this->assertEquals('112.00', $top_up_order->amount);
        $this->assertEquals('1', $top_up_order->vendor_id);
        $this->assertEquals('App\Models\Affiliate', $top_up_order->agent_type);
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals('1.12', $top_up_order->agent_commission);
    }

    public function testTopUpOrderSuccessfulResponseCodeAndMessage()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Recharge Request Successful", $data['message']);
    }

    public function testMaximumAmountValueBlocksTopup()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 1112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals("The amount may not be greater than 1000.", $data['message']);
    }

    public function testMinimumAmountValueBlocksTopup()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 9,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals("The amount must be at least 10.", $data['message']);
    }

    public function testTopupInvalidVendorId()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => 10,
            'connection_type' => 'prepaid',
            'amount' => 19,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals("The selected vendor id is invalid.", $data['message']);
    }

    public function testTopupNullVendorId()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => '',
            'connection_type' => 'prepaid',
            'amount' => 19,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testTopupTestWithoutVendorId()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'connection_type' => 'prepaid',
            'amount' => 19,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testTopupInternationalNumberInput()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+16469804741',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 19,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testTopupInsufficientBalance()
    {
        Affiliate::find(1)->update(["wallet" => 100]);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 1000,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You don't have sufficient balance to recharge.", $data['message']);
    }

    public function testTopupInputWithoutAmountValue()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testTopupInputNullAmountValue()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => ' ',
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testTopupInputNullConnectionType()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => ' ',
            'amount' => 10,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testTopupInputWithoutConnectionType()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'amount' => 10,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testTopupWithoutPin()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testTopupWithoutNullPin()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => ' ',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testTopupWithPendingUser()
    {
        Affiliate::find(1)->update(["verification_status" => 'pending']);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You are not verified to do this operation.", $data['message']);
    }

    public function testTopupWithRejectedUser()
    {
        Affiliate::find(1)->update(["verification_status" => 'rejected']);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You are not verified to do this operation.", $data['message']);
    }

    public function testTopupNullNumber()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 19,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testTopupTestWithoutNumberNpin()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 19,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.The password field is required.", $data['message']);
    }

    public function testTopupNullPinAndNumber()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'Mobile' => ' ',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 19,
            'password' => ' ',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.The password field is required.", $data['message']);
    }

    public function testTopupTestBlockNumber()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801678987656',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You can't recharge to a blocked number.", $data['message']);
    }

    public function testSuccessfulTopupDeductAmountFromAgentWallet()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 800,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $this->affiliate->reload();
        /**
         * Initial wallet balance = 10000 -> AffiliateFactory
         * Vendor Commission = 1% -> TopupVendorCommissionFactory
         * Wallet balance should be = 10000 - 800 + (800 % 1) = 9208
         **/
        $this->assertEquals(9208, $this->affiliate->wallet);
    }

    public function testTopupOtfShebaOtfCommissionCheck()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 104,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals(11.4, $top_up_order->otf_sheba_commission);
    }

    public function testTopupOtfOtfAgentCommissionCheck()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 104,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals(.6, $top_up_order->otf_agent_commission);
    }

    /**
     * @throws Throwable
     */
    public function testTopupOtfVendorIDnCheck()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 104,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals(1, $top_up_order->vendor_id);
    }

    public function testSuccessfulTopupAgentGenerateTransaction()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $affiliate_transactions = AffiliateTransaction::first();
        /**
         * Initial wallet balance = 10000 -> AffiliateFactory
         * Vendor Commission = 1% -> TopupVendorCommissionFactory
         * Topup Amount should be = 100 - (100 % 1) = 99
         **/
        $this->assertEquals($this->affiliate->id, $affiliate_transactions->affiliate_id);
        $this->assertEquals(99, $affiliate_transactions->amount);
    }

    public function testTopupGeneralUserAgentCommission()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals(1, $top_up_order->agent_commission);
    }

    /**
     * set specific commission against this affiliate
     */
    public function testTopupSpecificUserAgentCommission()
    {
        $this->logInWithMobileNEmail("+880162001019");
        $this->topUpVendorCommission = TopUpVendorCommission::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id,
            'agent_commission' => '0',
            'ambassador_commission' => '0',
            'type' => 'App\Models\Affiliate',
            'type_id' => 2,
        ]);

        // set fixed commission for regular user (all ready set)
        // topup function call for regular user

        // check regular agent wallet balance
        // check specific agent wallet balance
        // calculate affiliate commission

        // top up function call for specific user
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals(0, $top_up_order->agent_commission);
    }

    public function testTopupTransactionStoreAgentLatLng()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $Top_up_orders->agent_id);
        $this->assertEquals(null, $Top_up_orders->lat);
        $this->assertEquals(null, $Top_up_orders->lng);
    }

    public function testTopupTransactionStoreAgentIP()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $Top_up_orders->agent_id);
        $this->assertEquals("127.0.0.1", $Top_up_orders->ip);
    }

    public function testTopupTransactionStoreUserAgentType()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $Top_up_orders->agent_id);
        $this->assertEquals("App\Models\Affiliate", $Top_up_orders->agent_type);
    }

    public function testAffiliateSuccessfulTopupTransactionStoreAmbassadorCommission()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $Top_up_orders->agent_id);
        $this->assertEquals(0, $Top_up_orders->ambassador_commission);
    }

    public function testTopupTransactionStoreUserAgentInformation()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $Top_up_orders->agent_id);
        $this->assertEquals("Symfony", $Top_up_orders->user_agent);
    }

    public function testTopupTransactionStoreTransactionID()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $Top_up_orders->agent_id);
        $this->assertEquals("123456", $Top_up_orders->transaction_id);
    }

    public function testResponseCodeWhenAuthorizationTokenChanges()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01956154440',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 100,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token" . "jgfjh",
        ]);
        $data = $response->json();
        $this->assertEquals(401, $data['code']);
    }

    public function testTopUpOrderGatewayTimeoutResponseCodeAndMessage()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals("Failed", $top_up_order->status);
        $this->assertEquals("gateway_timeout", $top_up_order->failed_reason);
    }

    public function testTopUpOrderGatewayErrorResponseCodeAndMessage()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700888888',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->affiliate->id, $top_up_order->agent_id);
        $this->assertEquals("Failed", $top_up_order->status);
        $this->assertEquals("gateway_error", $top_up_order->failed_reason);
    }

    public function testTopUpOrderGatewayTimeoutThreeTimes()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $top_up_vendor = TopUpVendor::first();
        $this->assertEquals(0, $top_up_vendor->is_published);
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '+8801700999999',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Sorry, we don't support this operator at this moment.", $data['message']);
    }
}
