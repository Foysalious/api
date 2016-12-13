<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ServiceTest extends TestCase {
    public function testGetPartners()
    {
        $this->json('GET', 'service/1/partners')->seeJson(['code' => 200]);
    }

    public function testGetPartnerForLocation()
    {
        //no partner for this location
        $this->json('GET', 'service/1/location/8/partners')->seeJson(['service_partners' => [], 'code' => 404]);
        $this->json('GET', 'service/1/location/1/partners')->seeJson(['code' => 200]);
        //no service found
        $this->json('GET', 'service/10000000/location/1/partners')->seeJson(['code' => 500]);
    }

    public function testChangePartnerByLocation()
    {
        $options = array(0, 0, 0);
        $this->json('POST', 'service/8/1/change-partner', ['options' => $options])->seeJson(['code' => 200]);
        $options = array(0, 1, 2);
        //option not found
        $this->json('POST', 'service/8/1/change-partner', ['options' => $options])->seeJson(['code' => 404]);
    }

    public function testChangePartner()
    {
        $options = array(0, 0, 0);
        $this->json('POST', 'service/8/change-partner', ['options' => $options])->seeJson(['code' => 200]);
        $options = array(7, 19, 2);
        //option not found
        $this->json('POST', 'service/8/change-partner', ['options' => $options])->seeJson(['code' => 404]);
    }
}
