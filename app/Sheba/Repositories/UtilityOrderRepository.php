<?php


namespace App\Sheba\Repositories;


use App\Exceptions\NotFoundException;
use GuzzleHttp\Client;

class UtilityOrderRepository
{
    function getOrder($id)
    {
        $client = new Client();
        $contents = $client->request("GET", env("SHEBA_UTILITY_URL") . "/orders/" . $id)->getBody()->getContents();
        $response = json_decode($contents, true);
        return $this->getDefaultUtilityOrder($response);
    }

    function getDefaultUtilityOrder($response)
    {
        $order = new \stdClass();
        if ($response["code"] == 200) {
            $order->id = $response["data"]["order_id"];
            $order->price = $response["data"]["price"];
            $order->user_id = $response["data"]["user_id"];
            $order->user_type = $response["data"]["user_type"];
        } else {
            throw new NotFoundException("Utility User Not Found");
        }
        return $order;
    }

    function CompletePayment($order_id, $transaction_id)
    {
        $client = new Client();
        $contents = $client->request("POST", env("SHEBA_UTILITY_URL") . "/complete-payment/" . $order_id . '/' . $transaction_id)->getBody()->getContents();
        $contents = json_decode($contents, true);
        if ($contents["code"] != 200) throw new \Error("Can not complete payment" . $contents['message'] . $order_id);
        return $contents;
    }
}
