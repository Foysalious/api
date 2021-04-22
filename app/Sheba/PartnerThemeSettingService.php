<?php namespace App\Sheba;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
class PartnerThemeSettingService
{

    private $settings;
    private $theme_id;
    private $partnerId;
    private $client;

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
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/options', $data);
    }
}
