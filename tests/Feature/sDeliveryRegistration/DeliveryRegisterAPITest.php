<?php

namespace Tests\Feature\sDeliveryRegistration;


use Sheba\Dal\PartnerDeliveryInformation\Model;
use Tests\Feature\FeatureTestCase;

class DeliveryRegisterAPITest extends FeatureTestCase
{
    private $partnerDeliveryinfo;

    public function setUp()
    {
        parent::setUp();
        $this->logIn();

        $this->truncateTables([
            Model ::class
        ]);
        $this->partnerDeliveryinfo = factory(Model::class)->create();
    }

    public function testSuccessfulRegistration()
    {

        $response = $this-> post('v2/pos/delivery/register',[
            'name'=>'Sunerah Cardi',
            'company_ref_id'=>'IK0000987',
            'business_type'=>'E-Commerce',
            'address'=>'Plot#221/B,Cardi Street, Boyece Avenue',
            'district'=>'Noakhali',
            'thana'=>'Subarnachar',
            'fb_page_url'=>'https://fb.com/ssdsd00',
            'phone'=>'01987654321',
            'mobile'=>'01987654321',
            'payment_method'=>'cheque',
            'website'=>'sunerahcardi.xyz',
            'contact_name'=>'Sunerah Cardi',
            'email'=>'sunerah_cardi@gmail.com',
            'designation'=>'Manager',
            'account_type'=>'bank',
            'account_name'=>'SUNERAH CARDI',
            'account_number'=>'230156788990001',
            'bank_name'=>'Sonali Bank',
            'branch_name'=>'Subarnachar',
            'routing_number'=>'2450009'
        ],[
            'Authorization'=> "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();

    }
}