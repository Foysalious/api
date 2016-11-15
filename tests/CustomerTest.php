<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerTest extends TestCase {

    public function testLogin()
    {
        $this->json('POST', '/login', ['email' => 'arnabrahman@hotmail.com', 'password' => '123456'])
            ->seeJson([
                'msg' => 'Login successful',
                'code' => '200'
            ]);

        $this->json('POST', '/login', ['email' => 'arnabrman@hotmail.com', 'password' => '123456'])
            ->seeJson([
                'msg' => 'invalid_credentials',
                'code' => '404'
            ]);
    }

    public function testRegisterEmail()
    {
//        $this->json('POST', '/register-email', ['email' => 'abc@gmil.com', 'password' => 'xxxx'])->seeJson([
//            'code' => '200',
//            'msg' => 'Register with email successful'
//        ]);
//        $this->json('POST', '/register-email', ['email' => 'abc@gmil.com', 'password' => 'xxxx'])->seeJson([
//            'code' => '409',
//            'msg' => 'email already exists'
//        ]);
    }

    public function testRgisterMobile()
    {
        $this->post('/register-mobile', ['code' => 'jhafhjaf'])->seeStatusCode(500);
        $this->post('/login-with-kit', ['code' => 'jhafhjaf'])->seeStatusCode(500);

    }

    public function testResetPassword()
    {
//        $this->json('POST', '/forget-password', ['email' => 'abc@gmil.com'])->seeJson([
//            'msg' => 'Reset Password link send to email!',
//            'code' => '200'
//        ]);

        $this->json('POST', '/forget-password', ['email' => 'ab@gmil.com'])->seeJson([
            'msg' => 'This email doesn\'t exist',
            'code' => '404'
        ]);
    }
}
