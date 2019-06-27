<?php


namespace App\Sheba\Repositories;


use App\Exceptions\NotFoundException;
use GuzzleHttp\Client;

class UtilityOrderRepository
{
    function getOrder($id)
    {
        $client = new Client();
        $contents = $client->request("GET", env("SHEBA_UTILITY_URL") . "/history/" . $id . '?formatted=false')->getBody()->getContents();
        $response = json_decode($contents, true);
        return $this->getDefaultUtilityOrder($response);
    }

    function getDefaultUtilityOrder($response)
    {
        $order = new \stdClass();
        if ($response["code"] == 200) {
            $order->id = $response["data"]["id"];
            $order->price = $response["data"]["price"];
            $order->user_id = $response["data"]["user_id"];
            $order->user_type = $response["data"]["user_type"];
        } else {
            throw new NotFoundException("Utility User Not Found");
        }
        return $order;
    }

    function CompletePayment($order_id)
    {
        $client = new Client();
        $contents = $client->request("POST", env("SHEBA_UTILITY_URL") . "/complete-payment/" . $order_id)->getBody()->getContents();
    }
}
