<?php namespace Feature\TopUp;


use App\Models\Affiliate;
use App\Models\Profile;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Faker\Generator;
use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
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
            'authorization_request_id' => $this-> authorizationRequest->id,
            'token' => $this->token
        ]);
        TopUpVendor::where('name', 'Mock')->delete();
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

}