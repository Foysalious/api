<?php namespace Tests\Feature\TopUp;

use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Sheba\TopUp\Verification\VerifyPin;
use Tests\Feature\FeatureTestCase;

class TopupSettingAPITest extends FeatureTestCase
{
    /** @var $topUpVendor */
    private $topUpVendor;

    /** @var $topUpVendorCommission */
    private $topUpVendorCommission;

    public function setUp()
    {
        parent::setUp();
        $this->truncateTables([
            TopUpVendor::class,
            TopUpVendorCommission::class,
        ]);

        $this->logIn();

        $this->topUpVendor = factory(TopUpVendor::class)->create();
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);
    }

    public function testTopupSettingSuccessResponse()
    {
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals(1, $data['vendors'][0]['id']);
        $this->assertEquals('Mock', $data['vendors'][0]['name']);
        $this->assertEquals(1, $data['vendors'][0]['is_published']);
        $this->assertEquals('mock', $data['vendors'][0]['asset']);
        $this->assertEquals(1, $data['vendors'][0]['agent_commission']);
        $this->assertEquals(1, $data['vendors'][0]['is_prepaid_available']);
        $this->assertEquals(1, $data['vendors'][0]['is_postpaid_available']);
    }

    public function testTopupSettingVendorNameUpdate()
    {
        TopUpVendor::find(1)->update(["name" => 'mock2']);
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals(1, $data['vendors'][0]['id']);
        $this->assertEquals('mock2', $data['vendors'][0]['name']);
    }

    public function testTopupSettingVendorIsPublishedStatusChange()
    {
        TopUpVendor::find(1)->update(["is_published" => '0']);
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals([], $data['vendors']);
    }

    public function testTopupSettingVendorShebaCommissionUpdate()
    {
        TopUpVendor::find(1)->update(["sheba_commission" => '2']);
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $response->decodeResponseJson();
        $TopUpVendor = TopUpVendor::first();
        $this->assertEquals("2", $TopUpVendor->sheba_commission);
    }

    public function testTopupSettingVendorShebaCommissionCheck()
    {
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $response->decodeResponseJson();
        $TopUpVendor = TopUpVendor::first();
        $this->assertEquals(4, $TopUpVendor->sheba_commission);
    }

    public function testTopupSettingVendorGatewayCheck()
    {
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $response->decodeResponseJson();
        $TopUpVendor = TopUpVendor::first();
        $this->assertEquals("ssl", $TopUpVendor->gateway);
    }

    public function testTopupSettingVendorGatewaynUpdate()
    {
        TopUpVendor::find(1)->update(["gateway" => 'paywell']);
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $response->decodeResponseJson();
        $TopUpVendor = TopUpVendor::first();
        $this->assertEquals("paywell", $TopUpVendor->gateway);
    }

    public function testTopupSettingVendorAmountCheck()
    {
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $response->decodeResponseJson();
        $TopUpVendor = TopUpVendor::first();
        $this->assertEquals(100000, $TopUpVendor->amount);
    }

    public function testTopupSettingVendorRegexChange()
    {
        $response = $this->get('/v2/settings/top-up?bondhu_app');
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals('^(013|13|014|14|018|18|016|16|017|17|019|19|015|15)', $data['regex']['typing']);
        $this->assertEquals('^(?:\\+?88)?01[16|8]\\d{8}$', $data['regex']['from_contact']);
        $this->assertEquals('Currently, we’re supporting,Mock.', $data['regex']['error_message']);

    }

}
