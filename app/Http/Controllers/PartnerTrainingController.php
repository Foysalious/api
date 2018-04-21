<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class PartnerTrainingController extends Controller
{
    public function redirect(Request $request)
    {
        try {
            $manager_resource = $request->manager_resource;
            $manager_profile = $manager_resource->profile;
            if ($manager_profile->email && $manager_profile->mobile) {
                $is_valid_url = $this->urlValidationCheck($manager_profile);
                if ($is_valid_url) {
                    return api_response($request, $is_valid_url, 200, $is_valid_url);
                } else {
                    return api_response($request, $is_valid_url, 500);
                }
            }
            return api_response($request, null, 400, ['message' => 'Invalid login credential']);
        } catch (\Throwable $e) {
            dd($e);
            return api_response($request, null, 500);
        }
    }

    private function urlValidationCheck($profile)
    {
        $user_name = $profile->name;
        $user_email = $profile->email;
        $user_mobile = substr($profile->mobile, 1);

        $repto_base_url = env('REPTO_TRAINING_URL');
        $repto_username = env('REPTO_USERNAME');
        $client_name = env('REPTO_CLIENT_NAME');
        $access_key = env('REPTO_ACCESS_KEY');

        $url = "$repto_base_url/$repto_username?name=$user_name&email=$user_email&phone=$user_mobile&client_name=$client_name&access_key=$access_key";
        try {
            $client = new Client();
            $res = $client->request('GET', $url);
            $data = $res->getBody();
            $data = json_decode($data, true);
            if (isset($data['error'])) {
                return array('url' => null, 'message' => $data['error']);
            } else {
                return array('url' => $url);
            }
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return null;
        }

    }

}