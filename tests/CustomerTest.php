<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerTest extends TestCase {
    public function testGetCustomer()
    {
        $this->json('GET', 'customer/4?remember_token=3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2OTgzNzcsImV4cCI6MTQ4MTc4NDc3NywibmJmIjoxNDgxNjk4Mzc3LCJqdGkiOiI5MWQxYjk4NDU0MWYwMmU1ZDZjZDA3OGNlMzM2ZDVkYyJ9.TYRPUqrpmLu4gd8md96orLB4sVe5HFr_-S2mobNZpdk')
            ->seeJson(['code' => 200]);

        $this->json('GET', 'customer/5?remember_token=3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2OTgzNzcsImV4cCI6MTQ4MTc4NDc3NywibmJmIjoxNDgxNjk4Mzc3LCJqdGkiOiI5MWQxYjk4NDU0MWYwMmU1ZDZjZDA3OGNlMzM2ZDVkYyJ9.TYRPUqrpmLu4gd8md96orLB4sVe5HFr_-S2mobNZpdk')
            ->seeJson(['code' => 409]);
    }

    public function testFBIntegration()
    {
        $this->json('POST', 'customer/4/fb-integration?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2OTgzNzcsImV4cCI6MTQ4MTc4NDc3NywibmJmIjoxNDgxNjk4Mzc3LCJqdGkiOiI5MWQxYjk4NDU0MWYwMmU1ZDZjZDA3OGNlMzM2ZDVkYyJ9.TYRPUqrpmLu4gd8md96orLB4sVe5HFr_-S2mobNZpdk', ['remember_token' => '3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg'])
            ->seeJson(['status_code' => 500]);
    }

    public function testAddAddress()
    {
        $this->json('POST', 'customer/4/change-address?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2OTgzNzcsImV4cCI6MTQ4MTc4NDc3NywibmJmIjoxNDgxNjk4Mzc3LCJqdGkiOiI5MWQxYjk4NDU0MWYwMmU1ZDZjZDA3OGNlMzM2ZDVkYyJ9.TYRPUqrpmLu4gd8md96orLB4sVe5HFr_-S2mobNZpdk', ['remember_token' => '3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg', 'office_address' => 'office', 'address' => 'address'])
            ->seeJson(['code' => 200]);
    }

    public function testAddDeliveryAddress()
    {
        $this->json('POST', 'customer/4/add-delivery-address?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2OTgzNzcsImV4cCI6MTQ4MTc4NDc3NywibmJmIjoxNDgxNjk4Mzc3LCJqdGkiOiI5MWQxYjk4NDU0MWYwMmU1ZDZjZDA3OGNlMzM2ZDVkYyJ9.TYRPUqrpmLu4gd8md96orLB4sVe5HFr_-S2mobNZpdk', ['remember_token' => '3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg', 'delivery_address' => 'address'])
            ->seeJson(['code' => 200]);
    }

    public function testGetDeliveryInfo()
    {
        $this->json('GET', 'customer/4/get-delivery-info?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cLzE5Mi4xNjguMS4xMDlcL3NoZWJhX25ld19hcGlcL3B1YmxpY1wvdjFcL3JlZ2lzdGVyLXdpdGgtZmFjZWJvb2siLCJpYXQiOjE0ODE2OTgzNzcsImV4cCI6MTQ4MTc4NDc3NywibmJmIjoxNDgxNjk4Mzc3LCJqdGkiOiI5MWQxYjk4NDU0MWYwMmU1ZDZjZDA3OGNlMzM2ZDVkYyJ9.TYRPUqrpmLu4gd8md96orLB4sVe5HFr_-S2mobNZpdk&remember_token=3I4rrTlavJ0aBIVdbI32VMEEZIsS3dzH9v2k7vudnHbu6FBCUOFuftEbYwWg')
            ->seeJson(['code' => 200]);
    }

    public function testRemoveDeliveryAddress()
    {
//        $this->json('POST','')
    }
}