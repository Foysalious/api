<?php

namespace Sheba\Learning;


class GenderDetection
{

    public function set()
    {
        $profiles = \App\Models\Profile::where([['name', '>', ''], ['gender', null], ['gender_label', null], ['id', '<', 1000]])->get();
        $client = new \GuzzleHttp\Client();
        $count = 0;
        foreach ($profiles as $profile) {
            if (!empty($profile->name) && empty($profile->gender) && empty($profile->gender_label) && $count <= 190) {
                $res = $client->request('GET', "https://genderapi.io/api/?name=" . $profile->name . "&key=5c835974615dc558d6147d82");
                $count++;
                if ($response = json_decode($res->getBody())) {
                    $profile->gender_label = ucfirst($response->gender);
                    $profile->update();
                }
            }
        }
        return ['code' => 200, 'message' => "Success"];
    }
}