<?php

namespace Tests\Unit\Sheba\Accounting;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Unit\UnitTestCase;

class DueDepositApiTest extends UnitTestCase
{
    private $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiU2F5ZWQgWWVhbWluIEFyYWZhdCIsImltYWdlIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9wcm9maWxlcy8xNTg1NTYzNzAxX3Byb2ZpbGVfaW1hZ2VfMjU5NTgwLmpwZWciLCJwcm9maWxlIjp7ImlkIjoyNTk1ODAsIm5hbWUiOiJTYXllZCBZZWFtaW4gQXJhZmF0IiwiZW1haWxfdmVyaWZpZWQiOjB9LCJjdXN0b21lciI6eyJpZCI6MTg5ODA5fSwicmVzb3VyY2UiOnsiaWQiOjQ1MzIwLCJwYXJ0bmVyIjp7ImlkIjozODAxNSwibmFtZSI6IiIsInN1Yl9kb21haW4iOiJkYW5hLWNsYXNzaWMiLCJsb2dvIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9wYXJ0bmVycy9sb2dvcy8xNjAzMjU3NTQ2X2RhbmFfY2xhc3NpY18uanBnIiwiaXNfbWFuYWdlciI6dHJ1ZX19LCJwYXJ0bmVyIjpudWxsLCJtZW1iZXIiOm51bGwsImJ1c2luZXNzX21lbWJlciI6bnVsbCwiYWZmaWxpYXRlIjp7ImlkIjozOTQ1N30sImxvZ2lzdGljX3VzZXIiOm51bGwsImJhbmtfdXNlciI6bnVsbCwic3RyYXRlZ2ljX3BhcnRuZXJfbWVtYmVyIjpudWxsLCJhdmF0YXIiOnsidHlwZSI6InBhcnRuZXIiLCJ0eXBlX2lkIjozODAxNX0sImV4cCI6MTYyMjk2NDQ2NSwic3ViIjoyNTk1ODAsImlzcyI6Imh0dHA6Ly9hY2NvdW50cy5kZXYtc2hlYmEueHl6L2FwaS92My90b2tlbi9nZW5lcmF0ZSIsImlhdCI6MTYyMjM1OTY2NSwibmJmIjoxNjIyMzU5NjY1LCJqdGkiOiJxTGx1RnlTMWw2WXV3ZFhrIn0.P4vCDtGzRDrdULXSw_n3cYoGUbzucbRwBO20gBT8zVI';

    public function test_entry_type_due()
    {
        $response = $this->post(url('/v2/accounting/due-tracker'), [
            'amount' => 4440,
            'account_key' => 'cash',
            'date' => '2020-12-25 15:49:59',
            'note' => 'note',
            'attachments' => '',
            'entry_type' => 'due',
            'customer_id' => 568,
        ], [
            'Authorization' => $this->token
        ]);

        $id = json_decode($response->response->getContent())->data->id;
        $response->assertResponseOk();
        $response->seeJson([
            "code" => 200,
            "message" => "Successful",
            "data" => [
                "id" => $id,
                "amount" => 4440
            ]
        ]);
    }

    public function test_entry_type_deposit()
    {
        $response = $this->post(url('/v2/accounting/due-tracker'), [
            'amount' => 4440,
            'account_key' => 'cash',
            'date' => '2020-12-25 15:49:59',
            'note' => 'note',
            'attachments' => '',
            'entry_type' => 'deposit',
            'customer_id' => 568,
        ], [
            'Authorization' => $this->token
        ]);

        $id = json_decode($response->response->getContent())->data->id;
        $response->assertResponseOk();
        $response->seeJson([
            "code" => 200,
            "message" => "Successful",
            "data" => [
                "id" => $id,
                "amount" => 4440
            ]
        ]);
    }
}
