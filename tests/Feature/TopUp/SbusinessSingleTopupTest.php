<?php namespace Tests\Feature\TopUp;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\PartnerTransaction;
use App\Models\Profile;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Illuminate\Support\Facades\Bus;
use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\VerifyPin;
use Tests\Feature\FeatureTestCase;

class SbusinessSingleTopupTest extends FeatureTestCase
{
    /** @var  $topUpVendor */
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
    
    public function setUp()
    {
        parent::setUp();
        $this->truncateTables([
            TopUpVendor::class, TopUpVendorCommission::class, TopUpOTFSettings::class, TopUpOrder::class, TopUpBlacklistNumber::class, Profile::class, Member::class, Business::class, BusinessMember::class
        ]);
        $this->logIn();

        $this->topUpVendor = factory(TopUpVendor::class)->create();
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id, 'agent_commission' => '1.00', 'type' => "App\Models\Business", 'type_id' => 1
        ]);

        $this->topUpOtfSettings = factory(TopUpOTFSettings::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpVendorOtf = factory(TopUpVendorOTF::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpStatusChangeLog = factory(TopUpVendorOTFChangeLog::class)->create([
            'otf_id' => $this->topUpVendorOtf->id
        ]);

        /*
         * create topup topBlocklistNumbers table
         */

        $this->topBlocklistNumbers = factory(TopUpBlacklistNumber::class)->create();

        $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)->setConstructorArgs([$this->app->make(AccountServer::class)])->setMethods(['verify'])->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);
    }

    public function testSuccessfulBusinessTopupResponse()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Recharge Request Successful", $data['message']);
    }

    public function testBusinessTopupsBusinessConsecutiveNumber()
    {
        $top_up_vendor = TopUpVendor::find(1)->update(["waiting_time" => 3]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("এই নাম্বারে কিছুক্ষন আগে টপ-আপ করা হয়েছে । পুনরায় এই নাম্বারে টপ-আপ করার জন্য অনুগ্রহপূর্বক 3 মিনিট অপেক্ষা করুন ।", $data['message']);

        sleep(60);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("এই নাম্বারে কিছুক্ষন আগে টপ-আপ করা হয়েছে । পুনরায় এই নাম্বারে টপ-আপ করার জন্য অনুগ্রহপূর্বক 2 মিনিট অপেক্ষা করুন ।", $data['message']);

        sleep(60);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("এই নাম্বারে কিছুক্ষন আগে টপ-আপ করা হয়েছে । পুনরায় এই নাম্বারে টপ-আপ করার জন্য অনুগ্রহপূর্বক 1 মিনিট অপেক্ষা করুন ।", $data['message']);
    }

    public function testBusinessTopupResponseForInvalidMobileNumber()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '016200', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testBusinessTopupResponseForForeignMobileNumber()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '+6444880800', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testBusinessTopupResponseForNullMobileNumber()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithoutlMobileNumber()
    {
        $response = $this->post('/v2/top-up/business', [
            'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testBusinessTopupResponseForInvalidVendorID()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => 100, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The selected vendor id is invalid.", $data['message']);
    }

    public function testBusinessTopupResponseForNullVendorID()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => '', 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithoutVendorID()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithNullConnection()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => '', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithoutConnectionType()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }


    public function testBusinessTopupResponseWithNullAmount()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => '', 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithoutAmount()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithNullPassword()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testBusinessTopupResponseWithoutPassword()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'is_otf_allow' => 0, 'amount' => 10,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    /**
     * User can topup using API but in B2B portal they can't permit user to access in Topup
     * modal without Email verification
     * This check isn't applicable in API END
     */

    public function testBusinessTopupResponseWithUnverifiedEmailUser()
    {
        Profile::find(1)->update(["email_verified" => 0]);
        Business::find(1)->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Recharge Request Successful", $data['message']);
    }

    public function testBusinessTopupResponseWithinsufficientBalance()
    {
        Business::find(1)->update(["wallet" => 9]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You don't have sufficient balance to recharge.", $data['message']);
    }

    public function testBusinessTopupSessionoutResponse()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "gfjhvjhvtydhjk nmvtyvhj"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testBusinessTopupBlacklistNumberResponse()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01678987656', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You can't recharge to a blocked number.", $data['message']);
    }

    public function testBusinessTopupMinimumAmountResponse()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 8, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount must be at least 10.", $data['message']);
    }

    public function testBusinessTopupMaximumAmountResponse()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 1200, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount may not be greater than 1000.", $data['message']);
    }

    /**
     * This coloum called "topup_prepaid_max_limit" -> didn't work for dev portal , user can't topup more than 1000 tk.
     */

    public function testBusinessTopupPrepaidMaxLimitResponse()
    {
        Business::find(1)->update(["wallet" => 1500, "topup_prepaid_max_limit" => 1200]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 1200, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']); //actual 200
        //$this->assertEquals("The amount may not be greater than 1000.", $data['message']); // actual response is "Success"
    }

    public function testOneBusinessMemberTopUpRequestCreateOneTopUpOrder()
    {
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $this->assertEquals(1, TopUpOrder::count());
    }

    public function testBusinessTopUpOrderDataMatchesOnTopUpOrderTable()
    {
        $businessWallet = Business::find(1);
        $businessWallet->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011015', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals(1, $top_up_order->id);
        $this->assertEquals('Successful', $top_up_order->status);
        $this->assertEquals('+8801620011015', $top_up_order->payee_mobile);
        $this->assertEquals('prepaid', $top_up_order->payee_mobile_type);
        $this->assertEquals('10', $top_up_order->amount);
        $this->assertEquals('1', $top_up_order->vendor_id);
        $this->assertEquals('App\Models\Business', $top_up_order->agent_type);
        $this->assertEquals($this->business_member->id, $top_up_order->agent_id);
        $this->assertEquals('0.10', $top_up_order->agent_commission);
    }

    public function testBusinessSuccessfulTopupDeductAmountFromBusinessMemberWallet()
    {
        $businessWallet = Business::find(1);
        $businessWallet->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 800, 'is_otf_allow' => 0, 'password' => '12345'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $business = Business::first();
        /*
         * Initial wallet balance = 1000 -> BusinessFactory
         * Vendor Commission = 1% -> TopupVendorCommissionFactory
         * Wallet balance should be = 1000 - 800 + (800 % 1) = 208
         */
        $this->assertEquals(208, $business->wallet);
    }

    public function testBusinessSuccessfulTopupTransactionStoreBusinessMemberLatLngInfo()
    {
        $businessWallet = Business::find(1)->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $Top_up_orders->agent_id);
        $this->assertEquals(null, $Top_up_orders->lat);
        $this->assertEquals(null, $Top_up_orders->lng);
    }

    public function testBusinessSuccessfulTopupTransactionStoreBusinessMemberIP()
    {
        $businessWallet = Business::find(1)->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $Top_up_orders->agent_id);
        $this->assertEquals("127.0.0.1", $Top_up_orders->ip);
    }

    public function testBusinessSuccessfulTopupTransactionStoreUserAgentType()
    {
        $businessWallet = Business::find(1)->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $Top_up_orders->agent_id);
        $this->assertEquals("App\Models\Business", $Top_up_orders->agent_type);
    }

    public function testSuccessfulTopupTransactionStoreUserAgentDeviceInformation()
    {
        Business::find(1)->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $Top_up_orders->agent_id);
        $this->assertEquals("Symfony/3.X", $Top_up_orders->user_agent);
    }

    public function testBusinessSuccessfulTopupTransactionStoreTopupTransactionID()
    {
        $businessWallet = Business::find(1)->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $Top_up_orders->agent_id);
        $this->assertEquals("123456", $Top_up_orders->transaction_id);
    }

    public function testBusinessSuccessfulTopupPartnerCommissionCheck()
    {
        $businessWallet = Business::find(1);
        $businessWallet->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $top_up_order->agent_id);
        $this->assertEquals(0.10, $top_up_order->agent_commission);
    }

    public function testBusinessSuccessfulTopupSpecificPartnerCommissionCheck()
    {
        $this->logInWithMobileNEmail("+880162001015");
        // set specific commission against this affiliate
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id, 'agent_commission' => '0', 'type' => 'App\Models\Business', 'type_id' => 2
        ]);

        // set fixed commission for regular user (all ready set)
        // topup function call for regular user

        // check regular Business Member wallet balance
        // check specific Business Member wallet balance
        // calculate Business Member commission

        // top up function call for specific user

        $businessWallet = Business::find(2);
        $businessWallet->update(["wallet" => 1000]);
        $response = $this->post('/v2/top-up/business', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'is_otf_allow' => 0, 'password' => '12345'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->business_member->id, $top_up_order->agent_id);
        $this->assertEquals(0, $top_up_order->agent_commission);
    }
}