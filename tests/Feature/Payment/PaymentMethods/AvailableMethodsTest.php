<?php namespace Tests\Feature\Payment\PaymentMethods;

use Tests\Feature\FeatureTestCase;

/**
 * @author Zubayer alam <zubayer@sheba.xyz>
 */
class AvailableMethodsTest extends FeatureTestCase
{
    public function setUp(): void
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
