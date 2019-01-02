<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 1/2/2019
 * Time: 10:46 AM
 */

namespace App\Sheba\Eksheba;


use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EkshebaOrder
{

    public function generateOrder($token,$name,$amount)
    {
        $url = env('EKSHEBA_API_URL') . '/save' ;
        $data =
            [
                'token' => $token,
                'date' => Carbon::today()->toDateString(),
                'data' => array(
                    'name' => $name,
                    'gender' => 3,
                    'amount' => $amount,
                    'is_tribe' => 0,
                    'is_disable' => 0
                )
            ];
        try {
            $client = new Client();
            $response = $client->request('POST', $url, array('form_params' => $data));
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return ['message'=>$e->getMessage()];
        }
    }
    
}