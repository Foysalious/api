<?php namespace Tests\Feature\TopUp;


use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Illuminate\Support\Facades\Schema;
use Sheba\OAuth2\AccountServer;
use Sheba\TopUp\Verification\VerifyPin;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;

class SingleTopUpTest extends FeatureTestCase
{
    private $affiliate;
    private $topUpVendor;
    private $topUpVendorCommission;
    private $topUpOtfSettings;
    private $topUpVendorOtf;
    private $topUpStatusChangeLog;

    public function setUp()
    {
        parent::setUp();
        $this->truncateTables([
            TopUpVendor::class,
            TopUpVendorCommission::class,
            TopUpOTFSettings::class,
            TopUpOrder::class
        ]);
        $this->logIn();


        $this->topUpVendor = factory(TopUpVendor::class)->create();
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpOtfSettings = factory(TopUpOTFSettings::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpVendorOtf = factory(TopUpVendorOTF::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpStatusChangeLog= factory(TopUpVendorOTFChangeLog::class)->create([
            'otf_id' => $this->topUpVendorOtf->id
        ]);
        $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)
            ->setConstructorArgs([$this->app->make(AccountServer::class)])
            ->setMethods(['verify'])
            ->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);
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
        dd($this->token);
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

        $this->post('/v2/top-up/affiliate', [
            'mobile' => '01678242955',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 112,
            'password' => '12349'

        ], [
            'Authorization' => "Bearer $this->token"
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
