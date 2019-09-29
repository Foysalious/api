<?php namespace App\Sheba\Eksheba;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EkshebaOrder
{
    public function generateOrder($token, $name, $amount)
    {
        $url = env('EKSHEBA_API_URL') . '/save';
        $data = [
            'token' => $token,
            'date' => Carbon::today()->toDateString(),
            'data' => [
                'name' => $name,
                'gender' => 3,
                'amount' => $amount,
                'is_tribe' => 0,
                'is_disable' => 0
            ]
        ];

        try {
            $client = new Client();
            $response = $client->request('POST', $url, ['form_params' => $data]);
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return ['message' => $e->getMessage()];
        }
    }
}