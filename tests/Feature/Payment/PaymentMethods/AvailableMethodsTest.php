<?php namespace Tests\Feature\Payment\PaymentMethods;

use Tests\Feature\FeatureTestCase;

class AvailableMethodsTest extends FeatureTestCase
{
    /** @test */
    public function isPaymentMethodsAvailable()
    {
        $response = $this->json('GET', '/v2/payments');
        $response->assertResponseOk();
        $response->seeJsonStructure([
            'code',
            'payments',
            'message'
        ]);
    }

}
