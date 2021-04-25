<?php namespace Tests\Feature\Payment\PaymentMethods;

use Tests\Feature\FeatureTestCase;

class AvailableMethodsTest extends FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function isPaymentMethodsAvailable()
    {
        $response = $this->get('/v2/payments');
        $response->assertResponseOk();

        $response->seeJsonStructure([
            'code',
            'message',
            'payments' => [
                '*' => [ 'name', 'is_published', 'asset', 'method_name']
            ]
        ]);
    }
}
