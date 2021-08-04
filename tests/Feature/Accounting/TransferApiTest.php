<?php namespace Tests\Feature\Accounting;


class TransferApiTest extends AccountingFeatureTest
{
    public function test_transfer_api()
    {
        $response = $this->post(config('sheba.api_url') . '/v2/accounting/transfer', $this->getFormData(), [
            'Authorization' => $this->token ?? $this->generateToken()
        ]);

        $response->assertResponseOk();
        $response->seeJson([
            "code" => 200,
            "message" => "Successful",
        ]);
    }

    private function getFormData() : array {
        return [
            'amount' => 5000,
            'from_account_key' => 'cash',
            'to_account_key' => 'sheba_account',
            'date' => '2021-06-04 10:15:30',
        ];
    }
}