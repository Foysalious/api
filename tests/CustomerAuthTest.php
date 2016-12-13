<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerAuthTest extends TestCase {

    public function testRgisterMobile()
    {
        $this->post('/register-mobile', ['code' => 'jhafhjaf'])->seeStatusCode(500);
    }

    public function testRegisterEmail()
    {
        $this->json('POST', '/register-email', ['email' => 'abc@gmil.com', 'password' => 'xxxx'])
            ->seeJson(['code' => 200]);
        $this->json('POST', '/register-email', ['email' => 'arnabrahman@hotmail.com', 'password' => 'xxxx'])
            ->seeJson(['code' => 409]);
    }

    public function testLoginByEmail()
    {
        $this->json('POST', '/login', ['email' => 'sally@sheba.xyz', 'password' => '112233'])
            ->seeJson(['code' => 200]);

        $this->json('POST', '/login', ['email' => 'sally@sheba.xyz', 'password' => '123456'])
            ->seeJson(['code' => 404]);
    }

    public function testLoginByKit()
    {
        $this->post('/login-with-kit', ['code' => 'jhafhjaf'])->seeStatusCode(500);
    }


    public function testResetPassword()
    {
        $this->json('POST', '/forget-password', ['email' => 'arnabrahman@hotmail.com'])->seeJson(['code' => 200]);

        $this->json('POST', '/forget-password', ['email' => 'ab@gmil.com'])->seeJson(['code' => 404]);
    }
}
