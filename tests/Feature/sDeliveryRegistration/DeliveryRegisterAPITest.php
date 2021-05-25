<?php

namespace Tests\Feature\sDeliveryRegistration;


use App\Models\Partner;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use Sheba\Dal\PartnerDeliveryInformation\Model;
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
            Model ::class,
            Partner::class
        ]);
        $this->partner = factory(Partner::class)->create();
        $this->partnerDeliveryinfo = factory(Model::class)->create();
        $this->app->singleton(DeliveryServerClient::class,MockDeliveryServerClientRegister::class);
    }

    public function testSuccessfulRegistrationWithAllInfo()
    {
        //dd($this->partnerDeliveryinfo);
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
        //$this->assertEquals("Successful.", $data['message']);
    }
}
//    public function testAlreadyRegisteredNumberCannotBeAccepted()
//    {
//
//    }
//
//    public function testWithoutDistrict()
//    {
//
//    }
//
//    public function testWithoutThana()
//    {
//
//    }
//
//    public function testPaymentInfoInvalidResponse()
//    {
//
//    }

