<?php

/**
 * Khairun
 * 24th may
 */


namespace Tests\Mocks;


use App\Sheba\Partner\Delivery\DeliveryServerClient;

class MockDeliveryServerClient extends DeliveryServerClient
{

    public function post($uri, $data, $multipart = false)
    {

        if($uri == 'merchants/register') return $this->getRegistrationData();
       // if($uri == 'merchants/register' && $this->get422Registrationersponse()['code']==422) return $this->get422Registrationersponse();
        if($uri == 'orders') return $this->getOrderData();
        if($uri == 'orders/track' && $this->getDeliveryOrderCreatedStatus()['data']['status'] == "Created") return $this->getDeliveryOrderCreatedStatus();

    }

    private function getOrderData()
    {

        return json_decode('{
            "data": {
                "id": 16,
                "merchant_id": 1,
                "logistic_partner_id": 1,
                "cod_amount": 0,
                "uid": "ORD-1616491561-0016",
                "status": "Created",
                "weight": "1.5",
                "delivery_option": "regular",
                "product_description": "Dress",
                "delivery_charge": 3580,
                "payment_amount": 3580,
                "payment_status": "PAID",
                "delivery_address": {
                    "address": "bangla motor",
                    "thana": "Ramna",
                    "district": "Dhaka",
                    "person_name": "Rajib",
                    "contact_phone": "01845963548"
                },
                "logistic_partner": {
                    "id": 1,
                    "name": "paperfly",
                    "email": "user@paperfly.com",
                    "phone": "01700112233"
                },
                "created_at": "2021-03-23T09:26:01.000000Z"
            }
        }',true);


    }

    private function getRegistrationData()
    {

         json_decode ('{
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

/*    private function get422Registrationersponse()
    {

        return json_decode(json_encode([
            'code'=>422,
          //  'msg'=>'Successful'
        ]));
    }*/


    private function getDeliveryOrderCreatedStatus()
    {
        return json_decode ('{
          "data": {
                "id": 16,
                "merchant_id": 1,
                "logistic_partner_id": 1,
                "cod_amount": 0,
                "uid": "ORD-1616491561-0016",
                "status": "Created",
                "weight": 1.5,
                "tracking_number": "230321-0014-A3-A2",
                "delivery_option": "regular",
                "product_description": "Dress",
                "delivery_charge": 3580,
                "payment_amount": 3580,
                "payment_status": "PAID",
                "delivery_address": {
                      "address": "bangla motor",
                      "thana": "Ramna",
                      "district": "Dhaka",
                      "person_name": "Rajib",
                      "contact_phone": "01845963548"
                },
                "created_by": {
                      "name": "Hasan Ahmed",
                      "email": "test@gmail.com",
                      "phone": null,
                      "designation": "Manager"
                },
                "logistic_partner": {
                      "id": 1,
                      "name": "paperfly",
                      "email": "user@paperfly.com",
                      "phone": "01700112233"
                },
                "created_at": "2021-03-23T09:26:01.000000Z"
          }
        }',true );
    }




}