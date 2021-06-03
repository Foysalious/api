<?php namespace Tests\Feature\Accounting;


use GuzzleHttp\Client;
use Tests\Feature\FeatureTestCase;

class IncomeExpenseEntryApiTest extends FeatureTestCase
{
    private $token;

    public function test_income_entry_api()
    {
        $response = $this->post(config('sheba.api_url').'/v2/accounting/income', $this->getFormData(), [
            'Authorization' => $this->token ?? $this->generateToken()
        ]);

        $id = json_decode($response->response->getContent())->data->id;
        $response->assertResponseOk();
        $response->seeJson([
            "code" => 200,
            "message" => "Successful",
            "data" => [
                "id" => $id,
                "amount" => 1111
            ]
        ]);
    }


    public function test_expense_type_deposit()
    {
        $response = $this->post(config('sheba.api_url').'/v2/accounting/expense', $this->getFormData(), [
            'Authorization' => $this->token ?? $this->generateToken()
        ]);

        $id = json_decode($response->response->getContent())->data->id;
        $response->assertResponseOk();
        $response->seeJson([
            "code" => 200,
            "message" => "Successful",
            "data" => [
                "id" => $id,
                "amount" => 1111
            ]
        ]);
    }

    private function generateToken(): string
    {
        $client = new Client();
        $response = $client->get('https://accounts.dev-sheba.xyz/api/v3/token/generate?type=resource&token=TemAMQbHo8NES7nlEielwNw1EGTOKcQTC6jImGLNP4MLbFCjtvbeziGwlMd7&type_id=45320');
        $this->token = 'Bearer ' . \GuzzleHttp\json_decode($response->getBody())->token;
        return $this->token;
    }

    private function getFormData() : array {
        return [
            'amount' => 1111,
            'from_account_key' => 'cash',
            'to_account_key' => 'sheba_account',
            'date' => '2020-12-25 15:49:59',
            'note' => 'test case from api project',
        ];
    }

}