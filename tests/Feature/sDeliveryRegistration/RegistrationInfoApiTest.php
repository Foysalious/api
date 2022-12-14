<?php

namespace Tests\Feature\sDeliveryRegistration;

use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInfo;
use Tests\Feature\FeatureTestCase;

/**
 * @author Md Taufiqur Rahman Miraz <taufiqur.rahman@sheba.xyz>
 */
class RegistrationInfoApiTest extends FeatureTestCase
{
    private $partnerDeliveryinfo;

    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();

        $this->truncateTables([PartnerDeliveryInfo ::class]);

        $this->partner->update([
            'business_type' => 'Construction',
            'address'       => 'Dhaka 1229',
        ]);

        $this->partnerDeliveryinfo = PartnerDeliveryInfo::factory()->create([
            'partner_id' => $this->partner->id,
        ]);
    }

    public function testSuccessfulRegistrationInfo()
    {
        $response = $this->get('/v2/pos/delivery/register', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testWithWrongAuthorizationIsNotAccepted()
    {
        $response = $this->get('/v2/pos/delivery/register', [
            'Authorization' => "Bearer $this->token"."hkhckjsd",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }
}
