<?php


namespace Tests\Feature\sDeliveryRegistration;


use App\Models\Partner;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use Tests\Feature\FeatureTestCase;

class RegistrationInfoApiTest extends FeatureTestCase
{
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
    }

    public function testIsDeliveryRegisteredKeyOnDashboardApi()
    {

    }
}