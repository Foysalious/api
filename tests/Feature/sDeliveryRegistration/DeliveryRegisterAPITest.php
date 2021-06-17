<?php

namespace Tests\Feature\sDeliveryRegistration;


use App\Models\Partner;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInfo;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;
use Tests\Mocks\MockDeliveryServerClientRegister;

class DeliveryRegisterAPITest extends FeatureTestCase
{
    private $partnerDeliveryinfo;

    public function setUp()
    {
        parent::setUp();
        $this->logIn();

        $this->truncateTables([
            PartnerDeliveryInfo ::class
        ]);
        $this->partnerDeliveryinfo = factory(PartnerDeliveryInfo::class)->create([
            'partner_id'=>$this->partner->id
        ]);
        $this->app->singleton(DeliveryServerClient::class, MockDeliveryServerClient::class);
    }

    public function testSuccessfulRegistrationWithAllInfo()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'Sunerah Cardi',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testWithoutNameCannotBeRegistered()
    {
        //$this->partnerDeliveryinfo = Sheba\Dal\PartnerDeliveryInformation\Model::find(1);
        $response = $this->post('v2/pos/delivery/register', [
            'name' => '',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'Sunerah Cardi',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The name field is required.", $data['message']);
    }

    public function testWithoutDistrictCannotBeRegistered()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => '',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'Sunerah Cardi',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The district field is required.", $data['message']);
    }

    public function testWithoutThanaCannotBeRegistered()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => '',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'Sunerah Cardi',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The thana field is required.", $data['message']);
    }

    public function testEmptyMobileNoCannotBeAccepted()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'Sunerah Cardi',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The mobile field is required.", $data['message']);
    }

    public function testEmptyPaymentMethodCannotBeAccepted()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => '',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'Sunerah Cardi',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The payment method field is required.", $data['message']);
    }

    public function testWithoutContactNameCannotBeAccepted()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => '',
            'email' => 'sunerah_cardi@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The contact name field is required.", $data['message']);
    }

    public function testWithoutAccountTypeCannotBeAccepted()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'delivery man',
            'email' => 'deliveryman@gmail.com',
            'designation' => 'Manager',
            'account_type' => '',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => ''
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The account type field is required.", $data['message']);
    }

    public function testWithWrongAuthorization()
    {
        $response = $this->post('v2/pos/delivery/register', [
            'name' => 'Sunerah Cardi',
            'company_ref_id' => "hshj990000",
            'business_type' => 'Construction',
            'address' => 'Plot#221/B,Cardi Street, Boyece Avenue',
            'district' => 'Noakhali',
            'thana' => 'Subarnachar',
            'fb_page_url' => 'https://fb.com/ssdsd00',
            'phone' => '01678242967',
            'mobile' => '01678242967',
            'payment_method' => 'cheque',
            'website' => 'sunerahcardi.xyz',
            'contact_name' => 'delivery man',
            'email' => 'deliveryman@gmail.com',
            'designation' => 'Manager',
            'account_type' => 'bank',
            'account_name' => 'SUNERAH CARDI',
            'account_number' => '230156788990001',
            'bank_name' => 'Sonali Bank',
            'branch_name' => 'Subarnachar',
            'routing_number' => '2450009'
        ], [
            'Authorization' => "Bearer $this->token"."xjhsxks"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

}