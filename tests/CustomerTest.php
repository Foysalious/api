<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerTest extends TestCase {
    public function testGetCustomer()
    {
        $this->json('GET', 'customer/4?remember_token=3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2MzMzMzgsImV4cCI6MTQ4MTYzNjkzOCwibmJmIjoxNDgxNjMzMzM4LCJqdGkiOiI5MGYwNDZjYThiNGY5ZWQxOTg3Y2FlMDJkNzcxODAyZSJ9.F_5I66IuXsW-d_-hde9JrRf7-2Ib0BdZJ3WKS_m5Peo')
            ->seeJson(['code' => 200]);

        $this->json('GET', 'customer/5?remember_token=3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2MzMzMzgsImV4cCI6MTQ4MTYzNjkzOCwibmJmIjoxNDgxNjMzMzM4LCJqdGkiOiI5MGYwNDZjYThiNGY5ZWQxOTg3Y2FlMDJkNzcxODAyZSJ9.F_5I66IuXsW-d_-hde9JrRf7-2Ib0BdZJ3WKS_m5Peo')
            ->seeJson(['code' => 409]);
    }

    public function testFBIntegration()
    {
        $this->json('POST', 'customer/4/fb-integration?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2MzY2MjEsImV4cCI6MTQ4MTY0MDIyMSwibmJmIjoxNDgxNjM2NjIxLCJqdGkiOiJmNDU1MGQ3OWRhMzQ4MzQyN2QxMmNkYjNhYmQyYmNmMiJ9.cSs2VuD539XkVFD5BOFAD0DdCpGlaFLnvFNksy3WGIg')
            ->seeJson(['code' => 200]);
    }
}