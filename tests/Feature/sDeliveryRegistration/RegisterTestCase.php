<?php namespace Tests\Feature\sDeliveryRegistration;

use App\Models\PosOrder;
use App\Models\Profile;
use Factory\PartnerDeliveryInfoFactory;
use Sheba\Dal\PartnerDeliveryInformation\Contract;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use Sheba\Dal\PartnerDeliveryInformation\EloquentImplementation;
use Tests\Feature\FeatureTestCase;

class RegisterTestCase extends FeatureTestCase
{
    private $partnerDeliveryinfo;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            Model ::class
        ]);
        $this->logIn();

        $this->partnerDeliveryinfo = factory(Model::class)->create();
    }

    public function testDummy()
    {
        dd($this->partnerDeliveryinfo);
        $this->assertEquals(1,1);
    }
}