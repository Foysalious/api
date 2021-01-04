<?php namespace Feature\TopUp;


use App\Models\Affiliate;
use App\Models\Profile;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use Sheba\OAuth2\AccountServer;
use Sheba\TopUp\Verification\VerifyPin;
use Tests\Feature\FeatureTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;

class SingleTopUpTest extends FeatureTestCase
{
    private $profile;
    private $affiliate;
    private $authorizationRequest;
    private $authrorizationToken;
    private $topUpVendor;
    private $token;
    private $topUpVendorCommission;
    private $topUpOtfSettings;
    private $topUpVendorOtf;
    private $topUpStatusChangeLog;


    public function setUp()
    {
        parent::setUp();
        Profile::where('mobile', '+8801678242955')->delete();
        $this->profile = factory(Profile::class)->create();
        $this->affiliate = factory(Affiliate::class)->create([
            'profile_id' => $this->profile->id
        ]);

        $this->authorizationRequest = factory(AuthorizationRequest::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->token = JWTAuth::fromUser($this->profile, [
            'name' => $this->profile->name,
            'image' => $this->profile->pro_pic,
            'profile' => ['id' => $this->profile->id, 'name' => $this->profile->name, 'email_verified' => $this->profile->email_verified],
            'customer' => null,
            'resource' => null,
            'member' => null,
            'business_member' => null,
            'affiliate' => ['id' => $this->affiliate->id],
            'logistic_user' => null,
            'bank_user' => null,
            'strategic_partner_member' => null,
            'avatar' => null,
            "exp" => Carbon::now()->addDay()->timestamp
        ]);
        $this->authrorizationToken = factory(AuthorizationToken::class)->create([
            'authorization_request_id' => $this->authorizationRequest->id,
            'token' => $this->token
        ]);

        Schema::disableForeignKeyConstraints();
        TopUpVendor::truncate();
        TopUpVendorCommission::truncate();
        TopUpOTFSettings::truncate();
        TopUpOrder::truncate();
        Schema::enableForeignKeyConstraints();

        $this->topUpVendor = factory(TopUpVendor::class)->create();
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
                 'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpOtfSettings = factory(TopUpOTFSettings::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        //TopUpVendorOTF::truncate();
        $this->topUpVendorOtf = factory(TopUpVendorOTF::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        //TopUpVendorOTFChangeLog::truncate();
        $this->topUpStatusChangeLog= factory(TopUpVendorOTFChangeLog::class)->create([
            'otf_id' => $this->topUpVendorOtf->id
        ]);


    }

    public function testInvalidMobileNumberIsRejected()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => 0171404731411,
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testMobileNumberValidationResponseCode()
    {
        $response = $this->post('/v2/top-up/affiliate', [
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
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
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
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
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
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
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
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
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testOneTopUpRequestCreateOneTopUpOrder()
    {
        $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)
            ->setConstructorArgs([$this->app->make(AccountServer::class)])
            ->setMethods(['verify'])
            ->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);

        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        //$data = $response->decodeResponseJson();
        $this->assertEquals(1, TopUpOrder::count());

    }

    public function testTopUpOrderDataMatchesTopUpRequestData()
    {
        $verify_pin_mock= $this->getMockBuilder(VerifyPin::class)
            ->setConstructorArgs([$this->app->make(AccountServer::class)])
            ->setMethods(['verify'])
            ->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);

        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12345'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $top_up_order=TopUpOrder::first();
        $this->assertEquals(1,$top_up_order->id);
        $this->assertEquals('Successful',$top_up_order->status);
        $this->assertEquals('+8801678242955',$top_up_order->payee_mobile);
        $this->assertEquals('prepaid',$top_up_order->payee_mobile_type);
        $this->assertEquals('112',$top_up_order->amount);
        $this->assertEquals('1',$top_up_order->vendor_id);
        $this->assertEquals('App\Models\Affiliate',$top_up_order->agent_type);
        $this->assertEquals($this->affiliate->id,$top_up_order->agent_id);
        $this->assertEquals('1.12',$top_up_order->agent_commission);

    }
    public function testTopUpOrderSuccessfulResponseCodeAndMessage()
    {
        $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)
            ->setConstructorArgs([$this->app->make(AccountServer::class)])
            ->setMethods(['verify'])
            ->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);

        $response = $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Recharge Request Successful", $data['message']);

    }

}
