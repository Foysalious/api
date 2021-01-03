<?php namespace Feature\TopUp;


use App\Models\Affiliate;
use App\Models\Profile;
use App\Models\TopUpVendor;
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

class SingleTopUpTest extends FeatureTestCase
{
    private $profile;
    private $affiliate;
    private $authorizationRequest;
    private $authrorizationToken;
    private $topUpVendor;
    private $token;

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
        Schema::enableForeignKeyConstraints();
        $this->topUpVendor = factory(TopUpVendor::class)->create();
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

    public function testSuccessfulTopUpRequest()
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
        dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }


}
