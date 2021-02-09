<?php


namespace Tests\Feature\TopUp;


use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;
use Sheba\OAuth2\AccountServer;
use Sheba\TopUp\Verification\VerifyPin;
use Tests\Feature\FeatureTestCase;

class SmanagerTopupTest extends FeatureTestCase
{
    private $topUpVendor;
    private $topUpVendorCommission;
    private $topUpOtfSettings;
    private $topUpVendorOtf;
    private $topUpStatusChangeLog;
    private $topBlocklistNumbers;

    public function setUp()
    {
        parent::setUp();
        $this->truncateTables([
            TopUpVendor::class,
            TopUpVendorCommission::class,
            TopUpOTFSettings::class,
            TopUpOrder::class,
            TopUpBlacklistNumber::class,
            Profile::class,
            Partner::class,
            Resource::class,
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

        $this->topUpStatusChangeLog = factory(TopUpVendorOTFChangeLog::class)->create([
            'otf_id' => $this->topUpVendorOtf->id
        ]);

        /*
         * TODO
         * create topup topBlocklistNumbers table
         */

        $this->topBlocklistNumbers = factory(TopUpBlacklistNumber::class)->create();
         //dd($this->topBlocklistNumbers);

        $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)
            ->setConstructorArgs([$this->app->make(AccountServer::class)])
            ->setMethods(['verify'])
            ->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);

    }

    public function testSuccessfulTopupResponse()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Recharge Request Successful", $data['message']);
    }

    public function testTopupResponseForInvalidMobileNumber()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '016200110',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }
    public function testTopupResponseForForeignMobileNumber()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '+6444880800',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data['message']);
    }

    public function testTopupResponseForNullMobileNumber()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testTopupResponseWithoutlMobileNumber()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testTopupResponseForInvalidVendorID()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => 100,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The selected vendor id is invalid.", $data['message']);
    }

    public function testTopupResponseForNullVendorID()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => '',
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testTopupResponseWithoutVendorID()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The vendor id field is required.", $data['message']);
    }

    public function testTopupResponseWithNullConnection()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => '',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }
    public function testTopupResponseWithoutConnectionType()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The connection type field is required.", $data['message']);
    }

    public function testTopupResponseWithNullAmount()
    {
        $resourceNIDStatus = Profile::find(1);
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'postpaid',
            'amount' => '',
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }
    public function testTopupResponseWithoutAmount()
    {
        $resourceNIDStatus = Profile::find(1);
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'postpaid',
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The amount field is required.", $data['message']);
    }

    public function testTopupResponseWithNullPassword()
    {
        $resourceNIDStatus = Profile::find(1);
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'postpaid',
            'amount' => 10,
            'password' => '',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testTopupResponseWithoutPassword()
    {
        $resourceNIDStatus = Profile::find(1);
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'postpaid',
            'amount' => 10,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The password field is required.", $data['message']);
    }

    public function testTopupResponseWithUnverifiedUser()
    {

        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'postpaid',
            'amount' => 10,
            'password' => '98974',
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You are not verified to do this operation.", $data['message']);
    }

    public function testTopupSessionoutResponse()
    {
        $resourceNIDStatus = Profile::find(1);
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01620011019',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'postpaid',
            'amount' => 10,
            'password' => '98974',
        ], [
            'Authorization' => "gfjhvjhvtydhjk nmvtyvhj"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testTopupBlacklistNumberResponse()
    {
        $resourceNIDStatus = Profile::find(1);;
        $resourceNIDStatus->update(["nid_verified" => 1]);
        $response = $this->post('/v2/top-up/partner', [
            'mobile' => '01678987656',
            'vendor_id' => $this->topUpVendor->id,
            'connection_type' => 'prepaid',
            'amount' => 10,
            'password' => 12345,
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You can't recharge to a blocked number.", $data['message']);
    }






}

