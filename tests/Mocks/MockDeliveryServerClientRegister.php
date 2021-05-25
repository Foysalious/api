<?php


namespace Tests\Mocks;


use App\Sheba\Partner\Delivery\DeliveryServerClient;

class MockDeliveryServerClientRegister extends DeliveryServerClient
{
    public function post($uri, $data, $multipart = false)
    {
        //dd(121);
        return $this->getRegistrationData();

    }

    private function getRegistrationData()
    {
        return json_decode ('{
            "data": {
                "uid": "M-2021-0001",
                "paperfly_merchant_code": "M-1-6563",
                "name": "Kothao Ltd.",
                "product_nature": "dress",
                "address": "77/5, Block - A",
                "district": "Dhaka",
                "thana": "Khilgaon",
                "website": "abcd.com",
                "fb_page_url": "https://fb.com/ssdsd00",
                "phone": "01802823280",
                "payment_method": "beftn",
                "mfs_info": {
                    "account_type": "beftn",
                    "account_name": "Hasan Ahmed",
                    "bank_name": "Brac Bank",
                    "branch_name": "Khilgaon",
                    "account_number": "120655122121",
                    "routing_number": "465121212"
                },
                "contact_info": {
                    "name": "Hasan Ahmed",
                    "email": "test@gmail.com",
                    "phone": "01700112233",
                    "designation": "Manager"
                }
            }
        }',true );
    }
}