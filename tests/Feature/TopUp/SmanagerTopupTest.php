<?php namespace Tests\Feature\TopUp;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\Dal\SubscriptionWisePaymentGateway\Model;
use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;
use Sheba\ExpenseTracker\Repository\ExpenseTrackerClient;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerClient;
use Sheba\OAuth2\VerifyPin;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockAccountingEntryClient;
use Tests\Mocks\MockAccountServerClient;
use Tests\Mocks\MockExpenseClient;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class SmanagerTopupTest extends FeatureTestCase
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
            TopUpOTFSettings::class,
            TopUpOrder::class,
            TopUpBlacklistNumber::class,
            Profile::class,
            Partner::class,
            Resource::class,
            PartnerTransaction::class,
            Model::class,
            TopUpVendor::class,
            TopUpVendorCommission::class
        ]);

        $this->logIn();

        TopUpVendor::factory()->create();

        TopUpVendorCommission::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id, 'agent_commission' => '1.00', 'type' => "App\Models\Partner"
        ]);

        Model::factory()->create();

        TopUpOTFSettings::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id, 'applicable_gateways' => '["ssl","airtel"]', 'type' => 'App\Models\Partner', 'agent_commission' => '5.03',
        ]);

        TopUpVendorOTF::factory()->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        TopUpVendorOTFChangeLog::factory()->create([
            'otf_id' => $this->topUpVendorOtf->id
        ]);

        TopUpBlacklistNumber::factory()->create();

       /* $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)->setConstructorArgs([$this->app->make(AccountServer::class)])->setMethods(['verify'])->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);*/
        $this->app->singleton(ExpenseTrackerClient::class, MockExpenseClient::class);
        $this->app->singleton(AccountingEntryClient::class, MockAccountingEntryClient::class);
        $this->app->singleton(AccountServerClient::class, MockAccountServerClient::class);
    }

    public function testSuccessfulTopupResponse()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Recharge Request Successful", $data['message']);
    }

    public function testsManagerConsecutiveTopupNumber()
    {
        TopUpVendor::find(1)->update(["waiting_time" => 3]);
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("এই নাম্বারে কিছুক্ষন আগে টপ-আপ করা হয়েছে । পুনরায় এই নাম্বারে টপ-আপ করার জন্য অনুগ্রহপূর্বক 3 মিনিট অপেক্ষা করুন ।", $data['message']);

        sleep(60);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("এই নাম্বারে কিছুক্ষন আগে টপ-আপ করা হয়েছে । পুনরায় এই নাম্বারে টপ-আপ করার জন্য অনুগ্রহপূর্বক 2 মিনিট অপেক্ষা করুন ।", $data['message']);

        sleep(60);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("এই নাম্বারে কিছুক্ষন আগে টপ-আপ করা হয়েছে । পুনরায় এই নাম্বারে টপ-আপ করার জন্য অনুগ্রহপূর্বক 1 মিনিট অপেক্ষা করুন ।", $data['message']);
    }

    public function testTopupResponseForInvalidMobileNumber()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '016200110', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testTopupResponseForForeignMobileNumber()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+6444880800', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testTopupResponseForNullMobileNumber()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testTopupResponseWithoutlMobileNumber()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testTopupResponseForInvalidVendorID()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => 100, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The selected vendor id is invalid.", $data['message']);
    }

    public function testTopupResponseForNullVendorID()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => '', 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testTopupResponseWithoutVendorID()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testTopupResponseWithNullGatewayConnection()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => '', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testTopupResponseWithoutConnectionType()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testTopupResponseWithNullAmount()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => '', 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testTopupResponseWithoutAmount()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testTopupResponseWithNullPassword()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'password' => '',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testTopupResponseWithoutPassword()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testTopupResponseWithUnverifiedUser()
    {
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'password' => '98974',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You are not verified to do this operation.", $data['message']);
    }

    public function testTopupSessionOutResponse()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'postpaid', 'amount' => 10, 'password' => '98974',
        ], [
            'Authorization' => "gfjhvjhvtydhjk nmvtyvhj"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testTopupBlacklistNumberResponse()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01678987656', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You can't recharge to a blocked number.", $data['message']);
    }

    public function testTopupMinimumAmountResponse()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 8, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount must be at least 10.", $data['message']);
    }

    public function testTopupMaximumAmountResponse()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 1200, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount may not be greater than 1000.", $data['message']);
    }

    public function testTopupResponseInsufficientBalance()
    {
        Partner::find(1)->update(["wallet" => 10]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 100, 'password' => '12349'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You don't have sufficient balance to recharge.", $data['message']);
    }

    public function testTopupResponseWithRejectedUser()
    {
        Resource::find(1)->update(["status" => 'rejected']);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01956154440', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12349'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You are not verified to do this operation.", $data['message']);
    }

    public function testTopupResponseWithPendingUser()
    {
        Resource::find(1)->update(["status" => 'Pending']);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01956154440', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12349'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You are not verified to do this operation.", $data['message']);
    }

    public function testOnePartnerTopUpRequestCreateOneTopUpOrder()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $this->assertEquals(1, TopUpOrder::count());
    }

    public function testTopUpOrderDataMatchesOnTopUpOrderTable()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(["wallet" => 1000, 'package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 10, 'password' => '12345',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals(1, $top_up_order->id);
        $this->assertEquals("Successful", $top_up_order->status);
        $this->assertEquals('+8801620011019', $top_up_order->payee_mobile);
        $this->assertEquals('prepaid', $top_up_order->payee_mobile_type);
        $this->assertEquals('10', $top_up_order->amount);
        $this->assertEquals('1', $top_up_order->vendor_id);
        $this->assertEquals('App\Models\Partner', $top_up_order->agent_type);
        $this->assertEquals($this->partner->id, $top_up_order->agent_id);
        $this->assertEquals('0.10', $top_up_order->agent_commission);
    }

    public function testSuccessfulTopupDeductAmountFromPartnerWallet()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['wallet' => 1000, 'package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+8801620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 100, 'password' => '12345'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $partner = Partner::first();
        $this->partner->reload();
        /**
         * Initial wallet balance = 1000
         * Partner Subscription Package ID = 1
         * Subscription wise Partner Vendor Commission = 1% -> SubscriptionWisePaymentGatewaysFactory
         * Wallet balance should be = 1000 - 100 + (100 % 1) = 901
         **/
        $this->assertEquals('901', $partner->wallet);
    }

    public function testSuccessfulTopupOtfShebaOtfCommissionCheck()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(["wallet" => 1000, 'package_id' => 1]);

        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 104,
            'password' => '12349'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $top_up_order->agent_id);
        $this->assertEquals(11.88, $top_up_order->otf_sheba_commission);
    }

    public function testSuccessfulTopupOtfAgentOtfCommissionCheck()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(["wallet" => 1000, 'package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $top_up_order->agent_id);
        $this->assertEquals(0.12, $top_up_order->otf_agent_commission);

    }

    public function testManagerTopupOtfVendorIDCheck()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $top_up_order->agent_id);
        $this->assertEquals(1, $top_up_order->vendor_id);
    }

    public function testSuccessfulTopupTransactionStoreAgentLatLngInfo()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $Top_up_orders->agent_id);
        $this->assertEquals(null, $Top_up_orders->lat);
        $this->assertEquals(null, $Top_up_orders->lng);
    }

    public function testSuccessfulTopupTransactionStoreAgentIP()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $Top_up_orders->agent_id);
        $this->assertEquals("127.0.0.1", $Top_up_orders->ip);

    }

    public function testSuccessfulTopupTransactionStoreUserAgentType()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $Top_up_orders->agent_id);
        $this->assertEquals("App\Models\Partner", $Top_up_orders->agent_type);
    }

    public function testSuccessfulTopupTransactionStoreUserAgentDeviceInformation()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(['package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $Top_up_orders->agent_id);
        $this->assertEquals("Symfony/3.X", $Top_up_orders->user_agent);
    }

    public function testSuccessfulTopupTransactionStoreTopupTransactionID()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(["wallet" => 1000, 'package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $Top_up_orders = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $Top_up_orders->agent_id);
        $this->assertEquals("123456", $Top_up_orders->transaction_id);
    }

    public function testSuccessfulTopupPartnerCommissionCheck()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(["wallet" => 1000, 'package_id' => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $top_up_order->agent_id);
        $this->assertEquals(1.04, $top_up_order->agent_commission);
    }

    /**
     * set specific commission against this affiliate
     **/

    public function testSuccessfulTopupSpecificPartnerCommissionCheck()
    {
        $this->logInWithMobileNEmail("+8801956154440");
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id, 'agent_commission' => '0', 'ambassador_commission' => '0', 'type' => 'App\Models\Partner', 'type_id' => 2
        ]);

        /**
         * set fixed commission for regular user (all ready set)
         * topup function call for regular user
         * check regular partner wallet balance
         * check specific partner wallet balance
         * calculate partner commission
         * top up function call for specific user
         **/

        Profile::find(2)->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 104, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $top_up_order = TopUpOrder::first();
        $this->assertEquals($this->partner->id, $top_up_order->agent_id);
        $this->assertEquals(0, $top_up_order->agent_commission);

    }

    public function testSuccessfulTopupPartnerRechargeAmount()
    {
        Profile::find(1)->update(["nid_verified" => 1]);
        Partner::find(1)->update(["wallet" => 1000, 'package_id' => 1]);

        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019', 'vendor_id' => $this->topUpVendor->id, 'connection_type' => 'prepaid', 'amount' => 100, 'password' => '12349',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $response->decodeResponseJson();
        $this->partner->reload();
        $partner_transactions = PartnerTransaction::first();

        /**
         * Initial wallet balance = 10000 -> PartnerFactory
         * Vendor Commission = 1% -> TopupVendorCommissionFactory
         * Topup Amount should be = 100 - (100 % 1) = 99
         */
        $this->assertEquals($this->partner->id, $partner_transactions->partner_id);
        $this->assertEquals(99, $partner_transactions->amount);
    }
}

