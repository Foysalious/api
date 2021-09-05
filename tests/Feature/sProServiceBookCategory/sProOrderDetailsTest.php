<?php namespace Tests\Feature\sProServiceBookCategory;

use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

class sProOrderDetailsTest extends FeatureTestCase
{
    private $service;
    private $dummyToken;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Service::class);

        $this->logIn();

        $this->service = factory(Service::class)->create([
            'description_bn' => '["গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।"]',
            'category_id' => 1,
            'is_published_for_backend' => 1
        ]);

    }

    public function testSProOrderDetailsAPIWithServiceDetails()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals('work_start', $data["data"]["instruction_keys"][0]);
        $this->assertEquals('service_details', $data["data"]["instruction_keys"][1]);
        $this->assertEquals('work_end', $data["data"]["instruction_keys"][2]);
        $this->assertEquals('কাজের শুরুতে', $data["data"]["instructions"]["work_start"]["header"]);
        $this->assertEquals('কাজের শুরুতে কি কি করণীয়', $data["data"]["instructions"]["work_start"]["title"]);
        $this->assertEquals("সেবার টি-শার্ট পরে কাজে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][0]);
        $this->assertEquals("পরিষ্কার পরিচ্ছন্ন হয়ে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][1]);
        $this->assertEquals("মাস্ক এন্ড হ্যান্ড গ্লোভস পরিধান করতে হবে", $data["data"]["instructions"]["work_start"]["list"][2]);
        $this->assertEquals("সার্ভিসের জন্য প্রয়োজনীয় সরঞ্জাম সাথে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][3]);
        $this->assertEquals("কাস্টমারের সাথে সময় এবং স্থান প্রয়োজন হলে কনফার্ম করে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][4]);
        $this->assertEquals("কাজের বিবরণ", $data["data"]["instructions"]["service_details"]["header"]);
        $this->assertEquals("কাজের বিবরণ", $data["data"]["instructions"]["service_details"]["title"]);
        $this->assertEquals("গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", $data["data"]["instructions"]["service_details"]["list"][0]);
        $this->assertEquals("কাজের শেষে", $data["data"]["instructions"]["work_end"]["header"]);
        $this->assertEquals("কাজের শেষে কি কি করণীয়", $data["data"]["instructions"]["work_end"]["title"]);
        $this->assertEquals("ওয়ারেন্টিযুক্ত সার্ভিসের ক্ষেত্রে কাস্টমারকে সার্ভিস ওয়ারেন্টি সম্পর্কে জানতে হবে", $data["data"]["instructions"]["work_end"]["list"][0]);
        $this->assertEquals("সার্ভিস রিভিউ এন্ড রেটিং এর জন্য অনুরোধ করতে পারেন, কিন্তু ৫ স্টার দেয়ার জন্যে জোর দেয়া যাবে না", $data["data"]["instructions"]["work_end"]["list"][1]);
        $this->assertEquals("কাস্টমারের যদি অন্য কোন সেবার সার্ভিস প্রয়োজন হয়, তাহলে  অর্ডার তৈরি অথবা সার্ভিস রিকোয়েস্ট করে দিতে পারেন", $data["data"]["instructions"]["work_end"]["list"][2]);
        $this->assertEquals("বের হওয়ার আগে কাস্টমারকে সালাম দিতে হবে", $data["data"]["instructions"]["work_end"]["list"][3]);

    }

    public function testSProOrderDetailsAPIWithoutServiceDetails()
    {
        //arrange
        $this->service -> update(["description_bn" => ""]);

        //act
        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals('work_start', $data["data"]["instruction_keys"][0]);
        $this->assertEquals('work_end', $data["data"]["instruction_keys"][1]);
        $this->assertEquals('কাজের শুরুতে', $data["data"]["instructions"]["work_start"]["header"]);
        $this->assertEquals('কাজের শুরুতে কি কি করণীয়', $data["data"]["instructions"]["work_start"]["title"]);
        $this->assertEquals("সেবার টি-শার্ট পরে কাজে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][0]);
        $this->assertEquals("পরিষ্কার পরিচ্ছন্ন হয়ে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][1]);
        $this->assertEquals("মাস্ক এন্ড হ্যান্ড গ্লোভস পরিধান করতে হবে", $data["data"]["instructions"]["work_start"]["list"][2]);
        $this->assertEquals("সার্ভিসের জন্য প্রয়োজনীয় সরঞ্জাম সাথে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][3]);
        $this->assertEquals("কাস্টমারের সাথে সময় এবং স্থান প্রয়োজন হলে কনফার্ম করে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][4]);
        $this->assertEquals("কাজের শেষে", $data["data"]["instructions"]["work_end"]["header"]);
        $this->assertEquals("কাজের শেষে কি কি করণীয়", $data["data"]["instructions"]["work_end"]["title"]);
        $this->assertEquals("ওয়ারেন্টিযুক্ত সার্ভিসের ক্ষেত্রে কাস্টমারকে সার্ভিস ওয়ারেন্টি সম্পর্কে জানতে হবে", $data["data"]["instructions"]["work_end"]["list"][0]);
        $this->assertEquals("সার্ভিস রিভিউ এন্ড রেটিং এর জন্য অনুরোধ করতে পারেন, কিন্তু ৫ স্টার দেয়ার জন্যে জোর দেয়া যাবে না", $data["data"]["instructions"]["work_end"]["list"][1]);
        $this->assertEquals("কাস্টমারের যদি অন্য কোন সেবার সার্ভিস প্রয়োজন হয়, তাহলে  অর্ডার তৈরি অথবা সার্ভিস রিকোয়েস্ট করে দিতে পারেন", $data["data"]["instructions"]["work_end"]["list"][2]);
        $this->assertEquals("বের হওয়ার আগে কাস্টমারকে সালাম দিতে হবে", $data["data"]["instructions"]["work_end"]["list"][3]);

    }

    public function testSProOrderDetailsAPIWithMultipleServiceDetails()
    {
        //arrange
        $this->service -> update(["description_bn" => '["গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", 
                                                        "গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", 
                                                        "গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।"]']);

        //act
        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals('work_start', $data["data"]["instruction_keys"][0]);
        $this->assertEquals('service_details', $data["data"]["instruction_keys"][1]);
        $this->assertEquals('work_end', $data["data"]["instruction_keys"][2]);
        $this->assertEquals('কাজের শুরুতে', $data["data"]["instructions"]["work_start"]["header"]);
        $this->assertEquals('কাজের শুরুতে কি কি করণীয়', $data["data"]["instructions"]["work_start"]["title"]);
        $this->assertEquals("সেবার টি-শার্ট পরে কাজে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][0]);
        $this->assertEquals("পরিষ্কার পরিচ্ছন্ন হয়ে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][1]);
        $this->assertEquals("মাস্ক এন্ড হ্যান্ড গ্লোভস পরিধান করতে হবে", $data["data"]["instructions"]["work_start"]["list"][2]);
        $this->assertEquals("সার্ভিসের জন্য প্রয়োজনীয় সরঞ্জাম সাথে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][3]);
        $this->assertEquals("কাস্টমারের সাথে সময় এবং স্থান প্রয়োজন হলে কনফার্ম করে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][4]);
        $this->assertEquals("কাজের বিবরণ", $data["data"]["instructions"]["service_details"]["header"]);
        $this->assertEquals("কাজের বিবরণ", $data["data"]["instructions"]["service_details"]["title"]);
        $this->assertEquals("গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", $data["data"]["instructions"]["service_details"]["list"][0]);
        $this->assertEquals("গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", $data["data"]["instructions"]["service_details"]["list"][1]);
        $this->assertEquals("গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", $data["data"]["instructions"]["service_details"]["list"][2]);
        $this->assertEquals("কাজের শেষে", $data["data"]["instructions"]["work_end"]["header"]);
        $this->assertEquals("কাজের শেষে কি কি করণীয়", $data["data"]["instructions"]["work_end"]["title"]);
        $this->assertEquals("ওয়ারেন্টিযুক্ত সার্ভিসের ক্ষেত্রে কাস্টমারকে সার্ভিস ওয়ারেন্টি সম্পর্কে জানতে হবে", $data["data"]["instructions"]["work_end"]["list"][0]);
        $this->assertEquals("সার্ভিস রিভিউ এন্ড রেটিং এর জন্য অনুরোধ করতে পারেন, কিন্তু ৫ স্টার দেয়ার জন্যে জোর দেয়া যাবে না", $data["data"]["instructions"]["work_end"]["list"][1]);
        $this->assertEquals("কাস্টমারের যদি অন্য কোন সেবার সার্ভিস প্রয়োজন হয়, তাহলে  অর্ডার তৈরি অথবা সার্ভিস রিকোয়েস্ট করে দিতে পারেন", $data["data"]["instructions"]["work_end"]["list"][2]);
        $this->assertEquals("বের হওয়ার আগে কাস্টমারকে সালাম দিতে হবে", $data["data"]["instructions"]["work_end"]["list"][3]);

    }

    public function testSProOrderDetailsAPIWithInvalidAuthToken()
    {
        //arrange
        $this->dummyToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImltYWdlIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9yZXNvdXJjZXMvYXZhdGFyLzE2MjI1MjA3NDNfa2F6aWZhaGR6YWt3YW4uanBnIiwicHJvZmlsZSI6eyJpZCI6MjYyNTM1LCJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImVtYWlsX3ZlcmlmaWVkIjowfSwiY3VzdG9tZXIiOnsiaWQiOjE5MDUwMX0sInJlc291cmNlIjp7ImlkIjo0NjMzMSwicGFydG5lciI6eyJpZCI6MjE2NzA0LCJuYW1lIjoiIiwic3ViX2RvbWFpbiI6InNlcnZpY2luZy1iZCIsImxvZ28iOiJodHRwczovL3MzLmFwLXNvdXRoLTEuYW1hem9uYXdzLmNvbS9jZG4tc2hlYmFkZXYvaW1hZ2VzL3BhcnRuZXJzL2xvZ29zLzE2MjI0NDM4ODBfc2VydmljaW5nYmQucG5nIiwiaXNfbWFuYWdlciI6dHJ1ZX19LCJwYXJ0bmVyIjpudWxsLCJtZW1iZXIiOm51bGwsImJ1c2luZXNzX21lbWJlciI6bnVsbCwiYWZmaWxpYXRlIjpudWxsLCJsb2dpc3RpY191c2VyIjpudWxsLCJiYW5rX3VzZXIiOm51bGwsInN0cmF0ZWdpY19wYXJ0bmVyX21lbWJlciI6bnVsbCwiYXZhdGFyIjp7InR5cGUiOiJjdXN0b21lciIsInR5cGVfaWQiOjE5MDUwMX0sImV4cCI6MTYyNDM0ODg2OSwic3ViIjoyNjI1MzUsImlzcyI6Imh0dHA6Ly9hY2NvdW50cy5kZXYtc2hlYmEueHl6L2FwaS92My90b2tlbi9nZW5lcmF0ZSIsImlhdCI6MTYyMzc0NDA3MCwibmJmIjoxNjIzNzQ0MDcwLCJqdGkiOiJGcEJvT0V2NGNnekhweThWIn0.gWbCfYkrSfdIdv8GMRz4gFZXDRdIYR5XA_hR3CRMdn8";

        //act
        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions', [
            'Authorization' => "Bearer $this->dummyToken"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);

    }

    public function testSProOrderDetailsAPIWithoutAuthToken()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);

    }

    public function testSProOrderDetailsAPIWithSpecialCharacterAsServiceId()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/service/!@#%%^/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('404 Not Found', $data["message"]);

    }

    public function testSProOrderDetailsAPIWithInvalidUrl()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/services/' . $this->service->id . '/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('404 Not Found', $data["message"]);

    }

    public function testSProOrderDetailsAPIWithAlphabeticCharacterAsServiceId()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/service/abcde/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('404 Not Found', $data["message"]);

    }

    public function testSProOrderDetailsAPIWithInvalidServiceId()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/service/110/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Service not found', $data["message"]);

    }

//    public function testSProOrderDetailsAPIWithServiceDetailsUnpublished()
//    {
//        //arrange
//        $this->service->update([
//            'publication_status' => 0
//        ]);
//
//        //act
//        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions', [
//            'Authorization' => "Bearer $this->token"
//        ]);
//
//        $data = $response->decodeResponseJson();
//        dd($data);
//
//        //assert
//        $this->assertEquals(200, $data["code"]);
//        $this->assertEquals('Successful', $data["message"]);
//        $this->assertEquals('work_start', $data["data"]["instruction_keys"][0]);
//        $this->assertEquals('service_details', $data["data"]["instruction_keys"][1]);
//        $this->assertEquals('work_end', $data["data"]["instruction_keys"][2]);
//        $this->assertEquals('কাজের শুরুতে', $data["data"]["instructions"]["work_start"]["header"]);
//        $this->assertEquals('কাজের শুরুতে কি কি করণীয়', $data["data"]["instructions"]["work_start"]["title"]);
//        $this->assertEquals("সেবার টি-শার্ট পরে কাজে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][0]);
//        $this->assertEquals("পরিষ্কার পরিচ্ছন্ন হয়ে যেতে হবে", $data["data"]["instructions"]["work_start"]["list"][1]);
//        $this->assertEquals("মাস্ক এন্ড হ্যান্ড গ্লোভস পরিধান করতে হবে", $data["data"]["instructions"]["work_start"]["list"][2]);
//        $this->assertEquals("সার্ভিসের জন্য প্রয়োজনীয় সরঞ্জাম সাথে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][3]);
//        $this->assertEquals("কাস্টমারের সাথে সময় এবং স্থান প্রয়োজন হলে কনফার্ম করে নিতে হবে", $data["data"]["instructions"]["work_start"]["list"][4]);
//        $this->assertEquals("কাজের বিবরণ", $data["data"]["instructions"]["service_details"]["header"]);
//        $this->assertEquals("কাজের বিবরণ", $data["data"]["instructions"]["service_details"]["title"]);
//        $this->assertEquals("গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।", $data["data"]["instructions"]["service_details"]["list"][0]);
//        $this->assertEquals("কাজের শেষে", $data["data"]["instructions"]["work_end"]["header"]);
//        $this->assertEquals("কাজের শেষে কি কি করণীয়", $data["data"]["instructions"]["work_end"]["title"]);
//        $this->assertEquals("ওয়ারেন্টিযুক্ত সার্ভিসের ক্ষেত্রে কাস্টমারকে সার্ভিস ওয়ারেন্টি সম্পর্কে জানতে হবে", $data["data"]["instructions"]["work_end"]["list"][0]);
//        $this->assertEquals("সার্ভিস রিভিউ এন্ড রেটিং এর জন্য অনুরোধ করতে পারেন, কিন্তু ৫ স্টার দেয়ার জন্যে জোর দেয়া যাবে না", $data["data"]["instructions"]["work_end"]["list"][1]);
//        $this->assertEquals("কাস্টমারের যদি অন্য কোন সেবার সার্ভিস প্রয়োজন হয়, তাহলে  অর্ডার তৈরি অথবা সার্ভিস রিকোয়েস্ট করে দিতে পারেন", $data["data"]["instructions"]["work_end"]["list"][2]);
//        $this->assertEquals("বের হওয়ার আগে কাস্টমারকে সালাম দিতে হবে", $data["data"]["instructions"]["work_end"]["list"][3]);
//
//    }

}
