<?php namespace App\Sheba;


class PartnerThemeSettingService
{

    private $settings;
    private $theme_id;
    private $partnerId;


    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function setThemeID($theme_id)
    {
        $this->theme_id = $theme_id;
        return $this;
    }

    public function setSettiings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    private function makeData()
    {
        $data = [];
        $data['settings'] = $this->settings;
        $data['theme_id']  = $this->theme_id;
        $data['partner_id'] = $this->partnerId;
        return $data;
    }

    public function store()
    {
        $client = new \GuzzleHttp\Client();
        $data = $this->makeData();
        return $client->post('https://settings-smanager-webstore.dev-sheba.xyz/partner-settings', [
            'form_params' =>
                $data
            ,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ]);

    }
}
