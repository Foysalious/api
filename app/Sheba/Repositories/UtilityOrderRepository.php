<?php


namespace App\Sheba\Repositories;


use GuzzleHttp\Client;

class UtilityOrderRepository
{
    function getOrder($id)
    {
        $client = new Client();
        $contents = $client->request("get", env("SHEBA_UTILITY_URL") . "/history/" . $id . '?formatted=false')->getBody()->getContents();
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
            $order->id = 0;
            $order->price = 0;
            $order->user_id = 0;
            $order->user_type = "Customer";
        }
        return $order;
    }
}
