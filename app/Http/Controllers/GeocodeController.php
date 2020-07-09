<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\Map\Address;
use Sheba\Map\ReverseGeoCode;

class GeocodeController extends Controller
{
    public function reverseGeocode(Request $request, ReverseGeoCode $reverse_geoCode, Geo $geo)
    {
        $geo->setLat($request->lat)->setLng($request->lng);
        /** @var Address $address */
        $address = $reverse_geoCode->setGeo($geo)->getAddress();
        if (!$address->hasAddress()) return api_response($request, null, 404);
        return api_response($request, $address, 200, ['location' => ['address' => $address->getAddress()]]);
    }

    public function apple(Request $request)
    {
        $client_id = 'xyz.sheba.app';
        $client_secret = 'eyJraWQiOiJRNjZOOVkySEY2IiwiYWxnIjoiRVMyNTYifQ.eyJpc3MiOiI0OTdLWkFTQkpKIiwiaWF0IjoxNTk0Mjk0NzI4LCJleHAiOjE2MDk4NDY3MjgsImF1ZCI6Imh0dHBzOi8vYXBwbGVpZC5hcHBsZS5jb20iLCJzdWIiOiJ4eXouc2hlYmEuYXBwIn0.MfQTOoy3RVvCIilGeWj_aL36Z9B1wWRXfV8TVaMFufTO0zzQa-21oAEwyJz5n57FYdsdCdNAEWDlJLnyC0Ayrg
code:c13d159e246664ba59aa24659c988090a.0.mrxst.EYZvOlAIEr4dJyQPq3lC3A';
        $response = $this->http('https://appleid.apple.com/auth/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->code,
            'redirect_uri' => config('sheba.api_url') . '/v1/apple',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ]);

        if (!isset($response->access_token)) {
            echo '<p>Error getting an access token:</p>';
            echo '<pre>';
            print_r($response);
            echo '</pre>';
            echo '<p><a href="/">Start Over</a></p>';
            die();
        }

        echo '<h3>Access Token Response</h3>';
        echo '<pre>';
        print_r($response);
        echo '</pre>';


        $claims = explode('.', $response->id_token)[1];
        $claims = json_decode(base64_decode($claims));

        echo '<h3>Parsed ID Token</h3>';
        echo '<pre>';
        print_r($claims);
        echo '</pre>';

        die();
    }

    private function http($url, $params = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($params)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: curl', # Apple requires a user agent header at the token endpoint
        ]);
        $response = curl_exec($ch);
        return json_decode($response);
    }
}